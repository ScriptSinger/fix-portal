<?php

namespace Tests\Feature;

use App\Actions\TelegramQuestions\ConfirmRegistration;
use App\Jobs\ExpireTelegramQuestionPrompt;
use App\Services\TelegramPromptDeletion;
use App\Services\TelegramQuestionPromptCleanup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramQuestionPromptExpiryTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.telegram.bot_token' => 'test-token', 'services.telegram.api_url' => 'https://api.telegram.org', 'services.telegram.proxy' => null]);
        $this->travelTo(now()->startOfSecond());
    }

    public function test_old_expiry_job_and_scheduler_preserve_pending_invitation(): void
    {
        Http::fake();
        $id = $this->question();
        $this->travel(30)->days();
        (new ExpireTelegramQuestionPrompt($id, 500))->handle(app(TelegramPromptDeletion::class));
        app(TelegramQuestionPromptCleanup::class)->run();
        Http::assertNothingSent();
        $this->assertDatabaseHas('telegram_question_messages', ['id' => $id,
            'community_confirmation_status' => 'pending', 'confirmation_prompt_message_id' => 500]);
        $this->assertFalse(ConfirmRegistration::run($id, 999, -100123, true)['confirmed']);
        $this->assertTrue(ConfirmRegistration::run($id, 778, -100123, true)['confirmed']);
    }

    public function test_expiry_does_not_delete_successfully_confirmed_prompt(): void
    {
        Http::fake();
        $id = $this->question();
        $this->assertTrue(ConfirmRegistration::run($id, 778, -100123, true)['confirmed']);
        // The callback has edited the prompt and cleared its pending cleanup marker.
        DB::table('telegram_question_messages')->where('id', $id)->update(['confirmation_prompt_message_id' => null]);
        $this->travel(5)->minutes();
        (new ExpireTelegramQuestionPrompt($id, 500))->handle(app(TelegramPromptDeletion::class));
        Http::assertNothingSent();
        $this->assertDatabaseHas('telegram_question_messages', ['id' => $id, 'community_confirmation_status' => 'confirmed']);
    }

    public function test_legacy_expired_invitation_can_be_confirmed(): void
    {
        $id = $this->question();
        DB::table('telegram_question_messages')->where('id', $id)->update(['community_confirmation_status' => 'expired']);
        $this->travel(30)->days();
        $this->assertTrue(ConfirmRegistration::run($id, 778, -100123, true)['confirmed']);
    }

    public function test_cleanup_can_retry_after_api_failure_and_accepts_already_deleted_message(): void
    {
        $id = $this->question();
        ConfirmRegistration::run($id, 778, -100123, false);
        Http::fakeSequence()->push(['ok' => false, 'description' => 'temporary error'], 500)
            ->push(['ok' => false, 'description' => 'Bad Request: message to delete not found'], 400);
        $job = new ExpireTelegramQuestionPrompt($id, 500);
        try {
            $job->handle(app(TelegramPromptDeletion::class));
            $this->fail('Temporary API errors must be retried');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('temporary error', $exception->getMessage());
        }
        $this->assertDatabaseHas('telegram_question_messages', ['id' => $id, 'community_confirmation_status' => 'declined', 'confirmation_prompt_message_id' => 500]);
        $job->handle(app(TelegramPromptDeletion::class));
        $this->assertDatabaseHas('telegram_question_messages', ['id' => $id, 'confirmation_prompt_message_id' => null]);
    }

    public function test_confirmation_retry_delivers_result_even_when_callback_has_expired(): void
    {
        $id = $this->question();
        $payload = ['update_id' => 123456, 'callback_query' => [
            'id' => 'confirmation-retry', 'chat_instance' => 'test',
            'data' => "question_confirm:{$id}",
            'from' => ['id' => 778, 'is_bot' => false, 'first_name' => 'Author'],
            'message' => ['message_id' => 500, 'date' => now()->timestamp,
                'chat' => ['id' => -100123, 'type' => 'supergroup']],
        ]];
        Http::fakeSequence()
            ->push(['ok' => true, 'result' => true])
            ->push(['description' => 'temporary edit failure'], 500)
            ->push(['description' => 'query is too old'], 400)
            ->push(['ok' => true, 'result' => ['message_id' => 500]]);

        try {
            app(\App\Services\TelegramUpdateHandler::class)->handle(123456, $payload);
            $this->fail('Editing failure must remain retryable.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('temporary edit failure', $exception->getMessage());
        }
        $registeredAt = DB::table('telegram_question_messages')->where('id', $id)->value('registered_at');
        $this->assertNotNull($registeredAt);
        $this->assertDatabaseHas('telegram_question_messages', ['id' => $id, 'confirmation_prompt_message_id' => 500]);
        $this->travel(1)->minutes();
        app(\App\Services\TelegramUpdateHandler::class)->handle(123456, $payload);
        $this->assertDatabaseHas('telegram_question_messages', ['id' => $id,
            'registered_at' => $registeredAt, 'confirmation_prompt_message_id' => null]);
        Http::assertSentCount(4);
    }

    public function test_cleanup_recovers_confirmed_prompt_and_accepts_already_edited_message(): void
    {
        $id = $this->question();
        ConfirmRegistration::run($id, 778, -100123, true);
        ConfirmRegistration::run($id, 778, -100123, false);
        Http::fakeSequence()->push(['description' => 'temporary error'], 500)
            ->push(['description' => 'Bad Request: message is not modified'], 400);
        app(TelegramQuestionPromptCleanup::class)->run();
        $this->assertDatabaseHas('telegram_question_messages', ['id' => $id, 'confirmation_prompt_message_id' => 500]);
        app(TelegramQuestionPromptCleanup::class)->run();
        $this->assertDatabaseHas('telegram_question_messages', ['id' => $id,
            'community_confirmation_status' => 'confirmed', 'confirmation_prompt_message_id' => null]);
        Http::assertNotSent(fn ($request) => str_ends_with($request->url(), '/deleteMessage'));
        Http::assertSentCount(2);
    }

    private function question(): int
    {
        $number = random_int(1000000000, 2000000000);
        return DB::table('telegram_question_messages')->insertGetId([
            'telegram_chat_id' => -100123, 'telegram_message_id' => $number, 'telegram_update_id' => $number,
            'telegram_user_id' => 778, 'text' => 'Вопрос?', 'payload' => '{}',
            'status' => 'awaiting_confirmation', 'community_confirmation_status' => 'pending',
            'confirmation_prompt_message_id' => 500, 'confirmation_expires_at' => now()->addMinutes(5),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
