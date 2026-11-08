<?php

namespace Tests\Feature;

use App\Jobs\ProcessTelegramUpdate;
use App\Services\TelegramUpdateHandler;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class TelegramUpdateRetryTest extends TestCase
{
    use DatabaseTransactions;

    private function storeUpdate(array $state = []): void
    {
        DB::table('telegram_webhook_updates')->insert($state + [
            'update_id' => 987654, 'payload' => '{"update_id":987654}', 'received_at' => now(),
        ]);
    }

    public function test_failure_is_retried_after_delay_and_success_is_not_reprocessed(): void
    {
        $this->storeUpdate();
        $handler = $this->mock(TelegramUpdateHandler::class);
        $handler->shouldReceive('handle')->once()->with(987654, ['update_id' => 987654])
            ->andThrow(new RuntimeException('Temporary failure'));
        $job = new ProcessTelegramUpdate(987654);
        try {
            $job->handle($handler);
            $this->fail('The queue must receive the exception.');
        } catch (RuntimeException $e) {
            $this->assertSame('Temporary failure', $e->getMessage());
        }
        $this->assertDatabaseHas('telegram_webhook_updates', [
            'update_id' => 987654, 'attempts' => 1, 'processed_at' => null, 'processing_error' => 'Temporary failure',
        ]);
        $job->handle($handler); // A duplicate during backoff must not execute.
        $this->travel(61)->seconds();
        $handler = $this->mock(TelegramUpdateHandler::class);
        $handler->shouldReceive('handle')->once()->andReturnNull();
        $job->handle($handler);
        $job->handle($handler);
        $this->assertDatabaseHas('telegram_webhook_updates', [
            'update_id' => 987654, 'attempts' => 2, 'processing_error' => null, 'failed_at' => null,
        ]);
        $this->assertNotNull(DB::table('telegram_webhook_updates')->where('update_id', 987654)->value('processed_at'));
    }

    public function test_lease_prevents_concurrent_processing_and_recovers_after_worker_crash(): void
    {
        $this->storeUpdate(['attempts' => 1, 'next_attempt_at' => now()->addMinutes(5)]);
        $handler = $this->mock(TelegramUpdateHandler::class);
        $handler->shouldReceive('handle')->once()->andReturnUsing(function () use ($handler): void {
            (new ProcessTelegramUpdate(987654))->handle($handler);
        });
        (new ProcessTelegramUpdate(987654))->handle($handler);
        $this->travel(301)->seconds();
        (new ProcessTelegramUpdate(987654))->handle($handler);
        $this->assertDatabaseHas('telegram_webhook_updates', ['update_id' => 987654, 'attempts' => 2]);
    }

    public function test_recovery_dispatches_lost_job_but_excludes_completed_busy_and_exhausted_updates(): void
    {
        Bus::fake();
        $this->storeUpdate();
        $this->storeUpdate(['update_id' => 987655, 'processed_at' => now()]);
        $this->storeUpdate(['update_id' => 987656, 'next_attempt_at' => now()->addMinutes(5)]);
        $this->storeUpdate(['update_id' => 987657, 'attempts' => 5]);
        $this->artisan('telegram:updates-retry')->assertSuccessful();
        Bus::assertDispatchedTimes(ProcessTelegramUpdate::class, 1);
        Bus::assertDispatched(ProcessTelegramUpdate::class, fn ($job) => $job->updateId === 987654);
        $this->artisan('telegram:updates-retry', ['--update' => '987657'])->assertSuccessful();
        Bus::assertDispatched(ProcessTelegramUpdate::class, fn ($job) => $job->updateId === 987657);
    }

    public function test_webhook_enqueues_saved_update_and_duplicate_can_restore_lost_job(): void
    {
        Bus::fake();
        config(['services.telegram.environment' => app()->environment(), 'services.telegram.webhook_secret' => 'secret']);
        for ($i = 0; $i < 2; $i++) {
            $this->postJson('/api/telegram/webhook', ['update_id' => 987654], [
                'X-Telegram-Bot-Api-Secret-Token' => 'secret',
            ])->assertOk();
        }
        $this->assertDatabaseHas('telegram_webhook_updates', ['update_id' => 987654, 'processed_at' => null, 'attempts' => 0]);
        Bus::assertDispatchedTimes(ProcessTelegramUpdate::class, 2);
    }
}
