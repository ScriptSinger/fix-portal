<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Jobs\SendActivityEmail;
use App\Mail\ActivityDigest;
use App\Models\Question;
use App\Models\User;
use App\Services\ActivityMailer;
use App\Services\ActivityNotifier;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\Concerns\CreatesDiscussions;
use Tests\TestCase;

/** Письма «вам ответили» и «ответ отмечен решением»: расписание, правила отправки, отписка. */
class ActivityEmailTest extends TestCase
{
    use CreatesDiscussions, DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['mail.activity' => ['enabled' => true, 'delay_minutes' => 10, 'per_user_daily' => 5, 'daily_cap' => 150]]);
        // в тестах очередь синхронная и задержку не соблюдает: задачу запускаем вручную (send)
        Queue::fake();
    }

    private function author(array $over = []): User
    {
        return User::factory()->create($over + ['email_verified_at' => now(), 'email_notifications' => true]);
    }

    /** Ответ мастера под вопросом автора; уведомление создаётся тем же кодом, что и на сайте. */
    private function answer(Question $question, string $text = 'Проверьте крыльчатку насоса и фильтр'): void
    {
        $master = User::factory()->create();
        $comment = $this->commentOn($question, $master, $text);
        app(ActivityNotifier::class)->answered($comment, $master);
    }

    private function send(User $user): void
    {
        (new SendActivityEmail($user->id))->handle(app(ActivityMailer::class));
    }

    public function test_answers_in_one_window_schedule_a_single_delayed_job(): void
    {
        Queue::fake();
        $author = $this->author();
        $question = $this->makeQuestion($author);

        $this->answer($question);
        $this->answer($question, 'Ещё проверьте шланг слива');

        Queue::assertPushed(SendActivityEmail::class, 1);
        Queue::assertPushed(SendActivityEmail::class, fn ($job) => $job->userId === $author->id && $job->delay !== null);
    }

    public function test_a_new_window_opens_after_the_letter_is_sent(): void
    {
        Queue::fake();
        Mail::fake();
        $author = $this->author();
        $question = $this->makeQuestion($author);
        $this->answer($question);

        $this->send($author);
        $this->answer($question, 'Новый ответ уже после письма');

        Queue::assertPushed(SendActivityEmail::class, 2);
    }

    public function test_nothing_is_scheduled_when_mail_is_off_opted_out_or_unverified(): void
    {
        Queue::fake();
        $question = $this->makeQuestion($this->author(['email_notifications' => false]));
        $this->answer($question);

        $unverified = $this->makeQuestion($this->author(['email_verified_at' => null]));
        $this->answer($unverified);

        config(['mail.activity.enabled' => false]);
        $off = $this->makeQuestion($this->author());
        $this->answer($off);

        Queue::assertNothingPushed();
    }

    public function test_only_answers_and_solutions_go_by_mail_not_reactions(): void
    {
        Queue::fake();
        $author = $this->author();
        $master = User::factory()->create();
        $comment = $this->commentOn($this->makeQuestion($master), $author, 'Мой комментарий');

        app(ActivityNotifier::class)->reacted($comment, $master, '👍');

        Queue::assertNothingPushed();
    }

    public function test_marking_an_answer_as_solution_schedules_a_letter_to_its_author(): void
    {
        Queue::fake();
        $asker = User::factory()->create();
        $master = $this->author();
        $question = $this->makeQuestion($asker);
        $answer = $this->commentOn($question, $master, 'Заменил реле, помогло');

        app(ActivityNotifier::class)->solution($question, $answer, $asker);

        Queue::assertPushed(SendActivityEmail::class, fn ($job) => $job->userId === $master->id);
    }

    public function test_the_letter_has_subject_snippet_link_and_one_click_unsubscribe(): void
    {
        Mail::fake();
        $author = $this->author(['email' => 'author@example.invalid']);
        $question = $this->makeQuestion($author, ['title' => 'Не сливает воду стиральная машина']);
        $this->answer($question, 'Проверьте крыльчатку насоса и фильтр');

        $this->send($author);

        Mail::assertSent(ActivityDigest::class, function (ActivityDigest $mail) use ($author, $question) {
            $html = $mail->render();
            $unsubscribe = route('email.unsubscribe.show', ['token' => $author->fresh()->unsubscribe_token]);

            return $mail->hasTo('author@example.invalid')
                && $mail->envelope()->subject === 'Вам ответили на вопрос: Не сливает воду стиральная машина'
                && str_contains($html, 'Проверьте крыльчатку насоса и фильтр')
                && str_contains($html, route('questions.show', ['question' => $question->slug]) . '#c-')
                && str_contains($html, $unsubscribe)
                && $mail->headers()->text['List-Unsubscribe'] === "<{$unsubscribe}>"
                && $mail->headers()->text['List-Unsubscribe-Post'] === 'List-Unsubscribe=One-Click'
                && $mail->content()->text === 'emails.activity-text';
        });
    }

    public function test_several_answers_are_merged_into_one_letter(): void
    {
        Mail::fake();
        $author = $this->author();
        $question = $this->makeQuestion($author, ['title' => 'Греется плата и не включается']);
        $this->answer($question, 'Первый совет');
        $this->answer($question, 'Второй совет');

        $this->send($author);

        Mail::assertSent(ActivityDigest::class, 1);
        Mail::assertSent(ActivityDigest::class, fn ($mail) => $mail->envelope()->subject === 'Новые ответы на ваш вопрос: Греется плата и не включается (2)'
            && str_contains($mail->render(), 'Первый совет') && str_contains($mail->render(), 'Второй совет'));
    }

    public function test_answers_to_different_questions_get_a_general_subject(): void
    {
        Mail::fake();
        $author = $this->author();
        $this->answer($this->makeQuestion($author));
        $this->answer($this->makeQuestion($author));

        $this->send($author);

        Mail::assertSent(ActivityDigest::class, fn ($mail) => $mail->envelope()->subject === 'Новые ответы на ваши вопросы (2)');
    }

    public function test_solution_letter_has_its_own_subject(): void
    {
        Mail::fake();
        $asker = User::factory()->create();
        $master = $this->author();
        $question = $this->makeQuestion($asker, ['title' => 'Не отжимает барабан']);
        $answer = $this->commentOn($question, $master, 'Заменил ремень');
        app(ActivityNotifier::class)->solution($question, $answer, $asker);

        $this->send($master);

        Mail::assertSent(ActivityDigest::class, fn ($mail) => $mail->envelope()->subject === 'Ваш ответ отмечен решением: Не отжимает барабан'
            && str_contains($mail->render(), 'Спасибо за помощь'));
    }

    public function test_a_notification_goes_by_mail_only_once(): void
    {
        Mail::fake();
        $author = $this->author();
        $this->answer($this->makeQuestion($author));

        $this->send($author);
        $this->send($author);

        Mail::assertSent(ActivityDigest::class, 1);
        $this->assertNotNull($author->notifications()->first()->emailed_at);
    }

    public function test_no_letter_if_the_person_already_read_the_notification_on_the_site(): void
    {
        Mail::fake();
        $author = $this->author();
        $this->answer($this->makeQuestion($author));
        $author->unreadNotifications->markAsRead();

        $this->send($author);

        Mail::assertNothingSent();
    }

    public function test_no_letter_if_the_person_opted_out_while_it_waited(): void
    {
        Mail::fake();
        $author = $this->author();
        $this->answer($this->makeQuestion($author));
        $author->forceFill(['email_notifications' => false])->save();

        $this->send($author);

        Mail::assertNothingSent();
    }

    public function test_per_person_and_site_wide_daily_caps(): void
    {
        Mail::fake();
        config(['mail.activity.per_user_daily' => 1]);
        $author = $this->author();
        $this->answer($this->makeQuestion($author));
        $this->send($author);
        $this->answer($this->makeQuestion($author));
        $this->send($author);

        Mail::assertSent(ActivityDigest::class, 1);

        config(['mail.activity.per_user_daily' => 5, 'mail.activity.daily_cap' => 1]);
        $other = $this->author();
        $this->answer($this->makeQuestion($other));
        $this->send($other);

        Mail::assertSent(ActivityDigest::class, 1);
    }

    public function test_the_unsubscribe_page_asks_first_and_the_post_turns_letters_off(): void
    {
        $author = $this->author();
        $token = $author->unsubscribeToken();

        $this->get(route('email.unsubscribe.show', ['token' => $token]))->assertOk()->assertSee('Отключить письма');
        $this->assertTrue($author->fresh()->email_notifications, 'открытие ссылки (например, почтовым сканером) ничего не отключает');

        $this->post(route('email.unsubscribe', ['token' => $token]))->assertOk()->assertSee('Письма отключены');
        $this->assertFalse($author->fresh()->email_notifications);
        $this->get(route('email.unsubscribe.show', ['token' => $token]))->assertSee('Письма отключены');
    }

    public function test_unknown_tokens_are_not_found_and_the_one_click_post_skips_csrf(): void
    {
        $this->get(route('email.unsubscribe.show', ['token' => 'no-such-token']))->assertNotFound();
        $this->post(route('email.unsubscribe', ['token' => 'no-such-token']))->assertNotFound();

        $except = (new \ReflectionClass(VerifyCsrfToken::class))->getProperty('except');
        $this->assertContains('email/unsubscribe/*', $except->getValue(app(VerifyCsrfToken::class)));
    }

    public function test_the_token_is_created_once_and_stays_the_same(): void
    {
        $author = $this->author();

        $first = $author->unsubscribeToken();

        $this->assertSame(40, strlen($first));
        $this->assertSame($first, $author->fresh()->unsubscribeToken());
    }

    public function test_profile_switch_turns_letters_off_and_on_and_old_forms_do_not_touch_it(): void
    {
        $author = $this->author();

        $this->actingAs($author)->put(route('profile.update'), ['email_notifications' => '0'])->assertRedirect();
        $this->assertFalse($author->fresh()->email_notifications);

        $this->actingAs($author)->put(route('profile.update'), ['bio' => 'Мастер по холодильникам'])->assertRedirect();
        $this->assertFalse($author->fresh()->email_notifications, 'запрос без переключателя настройку не меняет');

        $this->actingAs($author)->put(route('profile.update'), ['email_notifications' => '1'])->assertRedirect();
        $this->assertTrue($author->fresh()->email_notifications);

        $this->actingAs($author)->get(route('profile.edit'))->assertOk()->assertSee('Присылать письма');
    }
}
