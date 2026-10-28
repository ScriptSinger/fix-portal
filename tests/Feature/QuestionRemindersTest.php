<?php

namespace Tests\Feature;

use App\Enums\AnnouncementKind;
use App\Enums\AnnouncementStatus;
use App\Jobs\RetractQuestionAnnouncement;
use App\Jobs\SendQuestionAnnouncement;
use App\Jobs\UpdateQuestionAnnouncement;
use App\Models\Question;
use App\Models\QuestionAnnouncement;
use App\Models\User;
use App\Services\Announcements\Channels\TelegramChannel;
use App\Services\Announcements\QuestionAnnouncer;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\Concerns\CreatesDiscussions;
use Tests\TestCase;

/** Вопросы без ответа: выборка, фильтр на сайте, одно напоминание в чат и еженедельная сводка. */
class QuestionRemindersTest extends TestCase
{
    use CreatesDiscussions, DatabaseTransactions;

    private function telegram(): void
    {
        config([
            'services.telegram' => ['bot_token' => '123456:SECRET-TOKEN', 'chat_id' => '-100123', 'proxy' => null, 'api_url' => 'https://api.telegram.org', 'thread_id' => null],
            'services.vk_group' => ['token' => null, 'group_id' => null, 'version' => '5.199'],
        ]);
    }

    /** Вопрос без ответа возрастом $hours часов, уже объявленный в чате (сообщение 55). */
    private function announced(int $hours = 72, array $over = []): Question
    {
        $question = $this->makeQuestion(User::factory()->create(), $over);
        DB::table('questions')->where('id', $question->id)->update(['created_at' => now()->subHours($hours)]);
        QuestionAnnouncement::create(['question_id' => $question->id, 'channel' => 'telegram', 'kind' => AnnouncementKind::New, 'status' => AnnouncementStatus::Sent, 'external_id' => '55']);

        return $question->refresh();
    }

    private function reminders(Question $question): int
    {
        return $question->announcements()->where('kind', AnnouncementKind::Reminder->value)->count();
    }

    public function test_unanswered_means_no_solution_and_no_comment_from_someone_else(): void
    {
        $author = User::factory()->create();
        $bare = $this->makeQuestion($author);
        $ownComment = $this->makeQuestion($author);
        $this->commentOn($ownComment, $author, 'Дополнение от автора');
        $answered = $this->makeQuestion($author);
        $this->commentOn($answered, User::factory()->create(), 'Совет от мастера');
        $solved = $this->makeQuestion($author, ['solved_at' => now()]);

        $ids = Question::unanswered()->pluck('id')->all();

        $this->assertContains($bare->id, $ids);
        $this->assertContains($ownComment->id, $ids, 'комментарий самого автора ответом не считается');
        $this->assertNotContains($answered->id, $ids);
        $this->assertNotContains($solved->id, $ids);
    }

    public function test_site_filter_lists_only_unanswered_questions_of_any_age(): void
    {
        $author = User::factory()->create();
        $old = $this->makeQuestion($author, ['title' => 'Очень старый вопрос без ответов']);
        DB::table('questions')->where('id', $old->id)->update(['created_at' => '2024-01-10 10:00:00']);
        $answered = $this->makeQuestion($author, ['title' => 'Вопрос с ответом мастера']);
        $this->commentOn($answered, User::factory()->create(), 'Ответ');

        $this->get(route('questions.index', ['status' => 'unanswered']))
            ->assertOk()
            ->assertSee('Очень старый вопрос без ответов')
            ->assertDontSee('Вопрос с ответом мастера')
            ->assertSee('Без ответов');
    }

    public function test_reminder_is_queued_once_for_an_old_enough_unanswered_question(): void
    {
        $this->telegram();
        Queue::fake();
        $question = $this->announced(72);

        $this->artisan('questions:remind')->assertSuccessful();
        $this->artisan('questions:remind')->assertSuccessful();

        $this->assertSame(1, $this->reminders($question));
        Queue::assertPushed(SendQuestionAnnouncement::class, 1);
    }

    public function test_only_questions_between_48_hours_and_7_days_are_reminded(): void
    {
        $this->telegram();
        Queue::fake();
        $tooNew = $this->announced(30);
        $tooOld = $this->announced(24 * 8);
        $fits = $this->announced(24 * 6);

        $this->artisan('questions:remind')->assertSuccessful();

        $this->assertSame(0, $this->reminders($tooNew));
        $this->assertSame(0, $this->reminders($tooOld));
        $this->assertSame(1, $this->reminders($fits));
    }

    public function test_answered_solved_and_unannounced_questions_are_skipped(): void
    {
        $this->telegram();
        Queue::fake();
        $answered = $this->announced();
        $this->commentOn($answered, User::factory()->create(), 'Совет мастера');
        $solved = $this->announced(72, ['solved_at' => now()]);
        $notSent = $this->announced();
        $notSent->announcements()->update(['status' => AnnouncementStatus::Pending, 'external_id' => null]);

        $this->artisan('questions:remind')->assertSuccessful();

        foreach ([$answered, $solved, $notSent] as $question) {
            $this->assertSame(0, $this->reminders($question));
        }
    }

    public function test_limit_per_run_and_dry_run(): void
    {
        $this->telegram();
        Queue::fake();
        $questions = [$this->announced(100), $this->announced(90), $this->announced(80)];

        $this->artisan('questions:remind', ['--dry-run' => true])->assertSuccessful();
        $this->assertSame(0, QuestionAnnouncement::where('kind', AnnouncementKind::Reminder->value)->count());

        $this->artisan('questions:remind', ['--limit' => 2])->assertSuccessful();
        $this->assertSame(2, QuestionAnnouncement::where('kind', AnnouncementKind::Reminder->value)->count());
        // сначала самые старые
        $this->assertSame(1, $this->reminders($questions[0]));
        $this->assertSame(1, $this->reminders($questions[1]));
        $this->assertSame(0, $this->reminders($questions[2]));
    }

    public function test_nothing_is_sent_when_no_channel_can_remind(): void
    {
        config(['services.telegram' => ['bot_token' => null, 'chat_id' => null, 'proxy' => null, 'api_url' => 'https://api.telegram.org', 'thread_id' => null]]);
        config(['services.vk_group' => ['token' => 'vk-token', 'group_id' => '123456', 'version' => '5.199']]);
        Queue::fake();
        $question = $this->announced();

        $this->artisan('questions:remind')->assertSuccessful();

        $this->assertSame(0, $this->reminders($question));
    }

    public function test_reminder_job_replies_to_the_original_message_with_a_polite_text(): void
    {
        $this->telegram();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 77]])]);
        $question = $this->announced(72, ['title' => 'Ищу прошивку для платы Indesit WISL']);
        app(QuestionAnnouncer::class)->remind($question);
        $reminder = $question->announcements()->where('kind', AnnouncementKind::Reminder->value)->firstOrFail();

        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/sendMessage')
            && $request['reply_parameters'] === ['message_id' => 55, 'allow_sending_without_reply' => true]
            && str_contains($request['text'], 'Этот вопрос пока без ответа. Если разбираетесь в теме, загляните, пожалуйста.')
            && str_contains($request['text'], 'Ищу прошивку для платы Indesit WISL')
            && str_contains($request['text'], route('questions.show', ['question' => $question->slug]))
            && ! str_contains($request['text'], 'суток'));
        $reminder->refresh();
        $this->assertSame(AnnouncementStatus::Sent, $reminder->status);
        $this->assertSame('77', $reminder->external_id);
    }

    public function test_reminder_title_is_escaped_for_telegram_html(): void
    {
        $question = (new Question())->forceFill(['id' => 9, 'slug' => 'x', 'title' => 'Не греет <b>вода</b> & пишет ошибку']);

        $text = (new TelegramChannel())->reminderText($question);

        $this->assertStringNotContainsString('<b>вода</b>', $text);
        $this->assertStringContainsString('&lt;b&gt;вода&lt;/b&gt; &amp; пишет', $text);
    }

    public function test_digest_text_declines_the_word_and_has_the_link_and_thanks(): void
    {
        $channel = new TelegramChannel();
        $url = 'https://ufamasters.ru/questions?status=unanswered';

        foreach ([1 => 'вопрос', 2 => 'вопроса', 5 => 'вопросов', 11 => 'вопросов', 21 => 'вопрос', 22 => 'вопроса', 112 => 'вопросов'] as $n => $word) {
            $this->assertStringContainsString("На сайте сейчас {$n} {$word} без ответа.", $channel->digestText($n, $url));
        }
        $text = $channel->digestText(23, $url);
        $this->assertStringContainsString($url, $text);
        $this->assertStringContainsString('Спасибо всем, кто помогает!', $text);
    }

    public function test_digest_command_sends_the_count_and_the_filter_link(): void
    {
        $this->telegram();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]])]);
        $before = Question::unanswered()->count();
        $this->makeQuestion(User::factory()->create());
        $this->makeQuestion(User::factory()->create());

        $this->artisan('questions:digest')->assertSuccessful();

        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/sendMessage')
            && str_contains($request['text'], 'На сайте сейчас ' . ($before + 2))
            && str_contains($request['text'], 'status=unanswered'));
    }

    public function test_digest_dry_run_sends_nothing(): void
    {
        $this->telegram();
        Http::fake();
        $this->makeQuestion(User::factory()->create());

        $this->artisan('questions:digest', ['--dry-run' => true])->assertSuccessful();

        Http::assertNothingSent();
    }

    public function test_editing_updates_only_the_announcement_not_the_reminder(): void
    {
        $this->telegram();
        Queue::fake();
        $question = $this->announced();
        QuestionAnnouncement::create(['question_id' => $question->id, 'channel' => 'telegram', 'kind' => AnnouncementKind::Reminder, 'status' => AnnouncementStatus::Sent, 'external_id' => '77']);

        app(QuestionAnnouncer::class)->refresh($question);

        Queue::assertPushed(UpdateQuestionAnnouncement::class, 1);
    }

    public function test_deleting_a_question_removes_both_the_announcement_and_the_reminder_from_the_chat(): void
    {
        $this->telegram();
        Queue::fake();
        $question = $this->announced();
        QuestionAnnouncement::create(['question_id' => $question->id, 'channel' => 'telegram', 'kind' => AnnouncementKind::Reminder, 'status' => AnnouncementStatus::Sent, 'external_id' => '77']);

        $question->delete();

        Queue::assertPushed(RetractQuestionAnnouncement::class, 2);
        Queue::assertPushed(RetractQuestionAnnouncement::class, fn ($job) => $job->externalId === '77');
    }

    public function test_commands_are_scheduled_in_ufa_time(): void
    {
        $events = collect(app(Schedule::class)->events());

        $remind = $events->first(fn ($e) => str_contains($e->command, 'questions:remind'));
        $digest = $events->first(fn ($e) => str_contains($e->command, 'questions:digest'));

        $this->assertNotNull($remind);
        $this->assertNotNull($digest);
        $this->assertSame('Asia/Yekaterinburg', (string) $remind->timezone);
        $this->assertSame('0 10 * * 1', $digest->expression);
    }
}
