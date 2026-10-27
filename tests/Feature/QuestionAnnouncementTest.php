<?php

namespace Tests\Feature;

use App\Enums\AnnouncementStatus;
use App\Jobs\SendQuestionAnnouncement;
use App\Jobs\UpdateQuestionAnnouncement;
use App\Models\Appliance;
use App\Models\QuestionAnnouncement;
use App\Models\User;
use App\Services\Announcements\QuestionAnnouncer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\Concerns\CreatesDiscussions;
use Tests\TestCase;

/** Новый вопрос уходит в чат мастеров: записи по каналам, очередь, повторы, статусы. */
class QuestionAnnouncementTest extends TestCase
{
    use CreatesDiscussions, DatabaseTransactions;

    private function telegram(bool $on = true): void
    {
        config(['services.telegram' => [
            'bot_token' => $on ? '123456:SECRET-TOKEN' : null,
            'chat_id' => $on ? '-5494489427' : null,
            'proxy' => null,
            'api_url' => 'https://api.telegram.org',
        ]]);
    }

    private function announcement(): QuestionAnnouncement
    {
        $question = $this->makeQuestion(User::factory()->create());

        return QuestionAnnouncement::create(['question_id' => $question->id, 'channel' => 'telegram']);
    }

    public function test_enabled_channel_gets_one_pending_record_and_one_job(): void
    {
        $this->telegram();
        Queue::fake();
        $question = $this->makeQuestion(User::factory()->create());

        app(QuestionAnnouncer::class)->announce($question);
        app(QuestionAnnouncer::class)->announce($question);

        $this->assertSame(1, QuestionAnnouncement::where('question_id', $question->id)->count());
        $this->assertSame(AnnouncementStatus::Pending, $question->announcements()->first()->status);
        Queue::assertPushed(SendQuestionAnnouncement::class, 1);
    }

    public function test_disabled_channel_does_nothing(): void
    {
        $this->telegram(false);
        Queue::fake();
        $question = $this->makeQuestion(User::factory()->create());

        app(QuestionAnnouncer::class)->announce($question);

        $this->assertSame(0, QuestionAnnouncement::count());
        Queue::assertNothingPushed();
    }

    public function test_publishing_a_question_queues_the_announcement(): void
    {
        $this->telegram();
        Queue::fake();
        $data = [
            'title' => 'Стиральная машина не сливает воду ' . uniqid(),
            'appliance_id' => Appliance::factory()->create()->id,
            'description' => 'Вода остаётся в баке, насос гудит. Фильтр чистил, шланг прочистил, ничего не помогло.',
        ];

        $this->actingAs(User::factory()->create())->post(route('questions.store'), $data)->assertRedirect();

        Queue::assertPushed(SendQuestionAnnouncement::class, 1);
    }

    public function test_editing_a_question_does_not_announce_again(): void
    {
        $this->telegram();
        Queue::fake();
        $author = User::factory()->create();
        $question = $this->makeQuestion($author);
        app(QuestionAnnouncer::class)->announce($question);

        $this->actingAs($author)->put(route('questions.update', ['question' => $question->slug]), [
            'title' => $question->title,
            'appliance_id' => $question->appliance_id,
            'description' => 'Дополнение: ' . str_repeat('подробности ', 10),
        ])->assertRedirect();

        Queue::assertPushed(SendQuestionAnnouncement::class, 1);
    }

    private function sentAnnouncementFor(\App\Models\Question $question, AnnouncementStatus $status = AnnouncementStatus::Sent): QuestionAnnouncement
    {
        return QuestionAnnouncement::create(['question_id' => $question->id, 'channel' => 'telegram', 'status' => $status, 'external_id' => '55']);
    }

    private function edit(\App\Models\Question $question, User $author, array $over = []): void
    {
        $this->actingAs($author)->put(route('questions.update', ['question' => $question->slug]), $over + [
            'title' => $question->title,
            'appliance_id' => $question->appliance_id,
            'description' => $question->description,
        ])->assertRedirect();
    }

    public function test_editing_a_sent_question_queues_a_delayed_update_of_the_message(): void
    {
        $this->telegram();
        Queue::fake();
        $author = User::factory()->create();
        $question = $this->makeQuestion($author);
        $announcement = $this->sentAnnouncementFor($question);

        $this->edit($question, $author, ['description' => 'Дополнение: ' . str_repeat('подробности ', 10)]);

        Queue::assertPushed(UpdateQuestionAnnouncement::class, fn ($job) => $job->announcementId === $announcement->id && $job->delay !== null);
    }

    public function test_editing_what_the_chat_does_not_show_changes_nothing(): void
    {
        $this->telegram();
        Queue::fake();
        $author = User::factory()->create();
        $question = $this->makeQuestion($author);
        $this->sentAnnouncementFor($question);

        $this->edit($question, $author);

        Queue::assertNotPushed(UpdateQuestionAnnouncement::class);
    }

    public function test_an_unsent_announcement_is_not_updated_it_goes_out_with_the_new_text(): void
    {
        $this->telegram();
        Queue::fake();
        $author = User::factory()->create();
        $question = $this->makeQuestion($author);
        $this->sentAnnouncementFor($question, AnnouncementStatus::Pending);

        $this->edit($question, $author, ['title' => 'Совсем другой заголовок вопроса ' . uniqid()]);

        Queue::assertNotPushed(UpdateQuestionAnnouncement::class);
    }

    public function test_update_job_edits_the_message_and_keeps_the_status(): void
    {
        $this->telegram();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 55]])]);
        $announcement = $this->sentAnnouncementFor($this->makeQuestion(User::factory()->create()));

        (new UpdateQuestionAnnouncement($announcement->id))->handle(app(QuestionAnnouncer::class));

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/editMessageText') && $request['message_id'] === 55);
        $this->assertSame(AnnouncementStatus::Sent, $announcement->refresh()->status);
    }

    public function test_update_job_does_nothing_for_an_unsent_announcement(): void
    {
        $this->telegram();
        Http::fake();
        $announcement = $this->sentAnnouncementFor($this->makeQuestion(User::factory()->create()), AnnouncementStatus::Pending);

        (new UpdateQuestionAnnouncement($announcement->id))->handle(app(QuestionAnnouncer::class));

        Http::assertNothingSent();
    }

    public function test_failed_update_does_not_mark_the_announcement_failed(): void
    {
        $announcement = $this->sentAnnouncementFor($this->makeQuestion(User::factory()->create()));

        (new UpdateQuestionAnnouncement($announcement->id))->failed(new \RuntimeException('Telegram: down'));

        $this->assertSame(AnnouncementStatus::Sent, $announcement->refresh()->status);
    }

    public function test_job_sends_and_marks_the_record_sent(): void
    {
        $this->telegram();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 99]])]);
        $announcement = $this->announcement();

        (new SendQuestionAnnouncement($announcement->id))->handle(app(QuestionAnnouncer::class));

        $announcement->refresh();
        $this->assertSame(AnnouncementStatus::Sent, $announcement->status);
        $this->assertSame('99', $announcement->external_id);
        $this->assertSame(1, $announcement->attempts);
        $this->assertNotNull($announcement->sent_at);
    }

    public function test_job_does_not_send_twice(): void
    {
        $this->telegram();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 99]])]);
        $announcement = $this->announcement();
        $announcement->update(['status' => AnnouncementStatus::Sent]);

        (new SendQuestionAnnouncement($announcement->id))->handle(app(QuestionAnnouncer::class));

        Http::assertNothingSent();
    }

    public function test_failure_is_recorded_without_secrets_and_rethrown_for_retry(): void
    {
        $this->telegram();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => false, 'description' => 'Bad Gateway'], 502)]);
        $announcement = $this->announcement();

        try {
            (new SendQuestionAnnouncement($announcement->id))->handle(app(QuestionAnnouncer::class));
            $this->fail('Сбой должен уйти на повтор');
        } catch (\RuntimeException) {
        }

        $announcement->refresh();
        $this->assertSame(AnnouncementStatus::Pending, $announcement->status);
        $this->assertSame(1, $announcement->attempts);
        $this->assertStringNotContainsString('SECRET-TOKEN', (string) $announcement->last_error);
    }

    public function test_last_failure_marks_the_record_failed(): void
    {
        $announcement = $this->announcement();

        (new SendQuestionAnnouncement($announcement->id))->failed(new \RuntimeException('Telegram: down'));

        $this->assertSame(AnnouncementStatus::Failed, $announcement->refresh()->status);
    }

    public function test_deleted_question_is_not_announced(): void
    {
        $this->telegram();
        Http::fake();
        $announcement = $this->announcement();
        $announcement->question->delete();

        (new SendQuestionAnnouncement($announcement->id))->handle(app(QuestionAnnouncer::class));

        Http::assertNothingSent();
    }
}
