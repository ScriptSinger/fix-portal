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

    public function test_expired_prompt_is_deleted_without_touching_question_or_publication(): void
    {
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => true])]);
        $id = $this->question();
        DB::table('telegram_question_publications')->insert(['telegram_question_message_id' => $id, 'status' => 'pending_moderation', 'created_at' => now(), 'updated_at' => now()]);
        $this->travel(5)->minutes();
        (new ExpireTelegramQuestionPrompt($id, 500))->handle(app(TelegramPromptDeletion::class));

        $this->assertDatabaseHas('telegram_question_messages', ['id' => $id, 'community_confirmation_status' => 'expired', 'confirmation_prompt_message_id' => null, 'registered_at' => null]);
        $this->assertDatabaseHas('telegram_question_publications', ['telegram_question_message_id' => $id, 'status' => 'pending_moderation']);
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/deleteMessage') && $request['chat_id'] === -100123 && $request['message_id'] === 500);
        $this->assertFalse(ConfirmRegistration::run($id, 778, -100123, true)['confirmed']);
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

    public function test_late_confirmation_is_rejected_even_before_cleanup_job_runs(): void
    {
        $id = $this->question();
        $this->travel(5)->minutes();
        $result = ConfirmRegistration::run($id, 778, -100123, true);
        $this->assertFalse($result['confirmed']);
        $this->assertTrue($result['delete']);
        $this->assertDatabaseHas('telegram_question_messages', ['id' => $id, 'community_confirmation_status' => 'expired', 'registered_at' => null]);
    }

    public function test_scheduler_cleans_up_even_if_delayed_job_ran_early(): void
    {
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => true])]);
        $id = $this->question();
        (new ExpireTelegramQuestionPrompt($id, 500))->handle(app(TelegramPromptDeletion::class));
        Http::assertNothingSent();
        $this->travel(5)->minutes();
        app(TelegramQuestionPromptCleanup::class)->run();
        $this->assertDatabaseHas('telegram_question_messages', ['id' => $id, 'community_confirmation_status' => 'expired', 'confirmation_prompt_message_id' => null]);
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/deleteMessage') && $request['message_id'] === 500);
    }

    public function test_cleanup_can_retry_after_api_failure_and_accepts_already_deleted_message(): void
    {
        $id = $this->question();
        $this->travel(5)->minutes();
        Http::fakeSequence()->push(['ok' => false, 'description' => 'temporary error'], 500)
            ->push(['ok' => false, 'description' => 'Bad Request: message to delete not found'], 400);
        $job = new ExpireTelegramQuestionPrompt($id, 500);
        try {
            $job->handle(app(TelegramPromptDeletion::class));
            $this->fail('Temporary API errors must be retried');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('temporary error', $exception->getMessage());
        }
        $this->assertDatabaseHas('telegram_question_messages', ['id' => $id, 'community_confirmation_status' => 'expired', 'confirmation_prompt_message_id' => 500]);
        $job->handle(app(TelegramPromptDeletion::class));
        $this->assertDatabaseHas('telegram_question_messages', ['id' => $id, 'confirmation_prompt_message_id' => null]);
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
