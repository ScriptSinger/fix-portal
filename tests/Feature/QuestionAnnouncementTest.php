<?php

namespace Tests\Feature;

use App\Enums\AnnouncementStatus;
use App\Jobs\RetractQuestionAnnouncement;
use App\Jobs\SendQuestionAnnouncement;
use App\Jobs\UpdateQuestionAnnouncement;
use App\Models\Appliance;
use App\Models\QuestionAnnouncement;
use App\Models\Reaction;
use App\Models\Reply;
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

    public function test_each_enabled_channel_gets_its_own_record_and_job(): void
    {
        $this->telegram();
        config(['services.vk_group' => ['token' => 'vk-token', 'group_id' => '123456', 'version' => '5.199']]);
        Queue::fake();
        $question = $this->makeQuestion(User::factory()->create());

        app(QuestionAnnouncer::class)->announce($question);

        $this->assertEqualsCanonicalizing(['telegram', 'vk'], $question->announcements()->pluck('channel')->all());
        Queue::assertPushed(SendQuestionAnnouncement::class, 2);
    }

    public function test_only_the_configured_channel_is_used(): void
    {
        $this->telegram(false);
        config(['services.vk_group' => ['token' => 'vk-token', 'group_id' => '123456', 'version' => '5.199']]);
        Queue::fake();
        $question = $this->makeQuestion(User::factory()->create());

        app(QuestionAnnouncer::class)->announce($question);

        $this->assertSame(['vk'], $question->announcements()->pluck('channel')->all());
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

    public function test_deleting_a_sent_question_queues_removal_of_its_message(): void
    {
        $this->telegram();
        Queue::fake();
        $question = $this->makeQuestion(User::factory()->create());
        $this->sentAnnouncementFor($question);

        $question->delete();

        Queue::assertPushed(RetractQuestionAnnouncement::class, fn ($job) => $job->channel === 'telegram' && $job->externalId === '55');
        $this->assertDatabaseMissing('question_announcements', ['question_id' => $question->id]);
    }

    public function test_deleting_a_question_that_was_never_sent_removes_nothing(): void
    {
        $this->telegram();
        Queue::fake();
        $question = $this->makeQuestion(User::factory()->create());
        $this->sentAnnouncementFor($question, AnnouncementStatus::Pending);

        $question->delete();

        Queue::assertNotPushed(RetractQuestionAnnouncement::class);
    }

    public function test_retract_job_deletes_the_message(): void
    {
        $this->telegram();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => true])]);

        (new RetractQuestionAnnouncement('telegram', '55'))->handle(app(QuestionAnnouncer::class));

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/deleteMessage') && $request['message_id'] === 55);
    }

    public function test_author_deleting_a_question_erases_replies_and_reactions_too(): void
    {
        $author = User::factory()->create();
        $other = User::factory()->create();
        $question = $this->makeQuestion($author);
        $comment = $this->commentOn($question, $other);
        $reply = Reply::forceCreate(['user_id' => $author->id, 'comment_id' => $comment->id, 'text' => 'Спасибо, проверю']);
        $reaction = Reaction::forceCreate(['user_id' => $author->id, 'reactable_type' => Reply::class, 'reactable_id' => $reply->id, 'emoji' => '👍']);

        $this->actingAs($author)->delete(route('questions.destroy', ['question' => $question->id]))->assertRedirect(route('questions.index'));

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);
        $this->assertDatabaseMissing('reactions', ['id' => $reaction->id]);
    }

    public function test_marking_a_question_solved_updates_its_message_once(): void
    {
        $this->telegram();
        Queue::fake();
        $author = User::factory()->create();
        $question = $this->makeQuestion($author);
        $this->sentAnnouncementFor($question);

        $this->actingAs($author)->post(route('questions.solution.store', ['id' => $question->id]))->assertRedirect();
        $this->actingAs($author)->post(route('questions.solution.store', ['id' => $question->id]))->assertRedirect();

        Queue::assertPushed(UpdateQuestionAnnouncement::class, 1);
    }

    public function test_unmarking_a_solved_question_updates_its_message(): void
    {
        $this->telegram();
        Queue::fake();
        $author = User::factory()->create();
        $question = $this->makeQuestion($author, ['solved_at' => now()]);
        $this->sentAnnouncementFor($question);

        $this->actingAs($author)->delete(route('questions.solution.destroy', ['id' => $question->id]))->assertRedirect();

        Queue::assertPushed(UpdateQuestionAnnouncement::class, 1);
    }

    private function bothChannelsSent(\App\Models\Question $question): void
    {
        $this->telegram();
        config(['services.vk_group' => ['token' => 'vk-token', 'group_id' => '123456', 'version' => '5.199']]);
        QuestionAnnouncement::create(['question_id' => $question->id, 'channel' => 'telegram', 'status' => AnnouncementStatus::Sent, 'external_id' => '55']);
        QuestionAnnouncement::create(['question_id' => $question->id, 'channel' => 'vk', 'status' => AnnouncementStatus::Sent, 'external_id' => '987']);
    }

    public function test_editing_a_question_updates_only_channels_that_can_modify(): void
    {
        Queue::fake();
        $author = User::factory()->create();
        $question = $this->makeQuestion($author);
        $this->bothChannelsSent($question);

        $this->edit($question, $author, ['description' => 'Дополнение: ' . str_repeat('подробности ', 10)]);

        // VK ключом сообщества править записи не даёт: обновляется только Telegram
        Queue::assertPushed(UpdateQuestionAnnouncement::class, 1);
    }

    public function test_marking_solved_updates_only_channels_that_can_modify(): void
    {
        Queue::fake();
        $author = User::factory()->create();
        $question = $this->makeQuestion($author);
        $this->bothChannelsSent($question);

        $this->actingAs($author)->post(route('questions.solution.store', ['id' => $question->id]))->assertRedirect();

        Queue::assertPushed(UpdateQuestionAnnouncement::class, 1);
    }

    public function test_deleting_a_question_removes_its_message_only_where_it_can(): void
    {
        Queue::fake();
        $question = $this->makeQuestion(User::factory()->create());
        $this->bothChannelsSent($question);

        $question->delete();

        Queue::assertPushed(RetractQuestionAnnouncement::class, 1);
        Queue::assertPushed(RetractQuestionAnnouncement::class, fn ($job) => $job->channel === 'telegram' && $job->externalId === '55');
        Queue::assertNotPushed(RetractQuestionAnnouncement::class, fn ($job) => $job->channel === 'vk');
    }

    public function test_vk_update_and_retract_jobs_do_nothing_even_if_queued_earlier(): void
    {
        $this->bothChannelsSent($question = $this->makeQuestion(User::factory()->create()));
        Http::fake(['api.vk.com/*' => Http::response(['response' => 1]), 'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true])]);
        $vk = QuestionAnnouncement::where(['question_id' => $question->id, 'channel' => 'vk'])->firstOrFail();

        (new UpdateQuestionAnnouncement($vk->id))->handle(app(QuestionAnnouncer::class));
        (new RetractQuestionAnnouncement('vk', '987'))->handle(app(QuestionAnnouncer::class));

        // задачи, поставленные до этого изменения, завершаются без обращения к VK и без ошибок
        Http::assertNothingSent();
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
