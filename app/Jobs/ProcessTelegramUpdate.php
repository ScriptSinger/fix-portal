<?php

namespace App\Jobs;

use App\Services\TelegramUpdateHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProcessTelegramUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public const MAX_ATTEMPTS = 5;

    public int $tries = self::MAX_ATTEMPTS;

    public int $timeout = 60;

    public function __construct(public int $updateId) {}

    public function backoff(): array
    {
        return [60, 300, 900, 3600];
    }

    public function handle(TelegramUpdateHandler $handler): void
    {
        $query = DB::table('telegram_webhook_updates')->where('update_id', $this->updateId);

        // Atomic lease shared by HTTP, workers and recovery. Longer than the worker timeout.
        $claimed = (clone $query)->whereNull('processed_at')
            ->where('attempts', '<', self::MAX_ATTEMPTS)
            ->where(fn ($q) => $q->whereNull('next_attempt_at')->orWhere('next_attempt_at', '<=', now()))
            ->update(['next_attempt_at' => now()->addMinutes(5), 'attempts' => DB::raw('attempts + 1')]);

        if (! $claimed) {
            return;
        }

        $update = $query->first();
        try {
            $handler->handle($this->updateId, json_decode($update->payload, true, 32, JSON_THROW_ON_ERROR));
            $query->update([
                'processed_at' => now(), 'failed_at' => null,
                'processing_error' => null, 'next_attempt_at' => null,
            ]);
        } catch (Throwable $exception) {
            $query->update([
                'failed_at' => now(),
                'processing_error' => mb_substr($exception->getMessage(), 0, 2000),
                'next_attempt_at' => now()->addSeconds($this->backoff()[min($update->attempts - 1, 3)]),
            ]);

            throw $exception;
        }
    }
}
