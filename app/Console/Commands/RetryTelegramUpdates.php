<?php

namespace App\Console\Commands;

use App\Jobs\ProcessTelegramUpdate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RetryTelegramUpdates extends Command
{
    protected $signature = 'telegram:updates-retry {--update= : Restart attempts for one update after its retry/lease delay}';

    protected $description = 'Queue unprocessed Telegram updates, including interrupted and failed attempts';

    public function handle(): int
    {
        $query = DB::table('telegram_webhook_updates')->whereNull('processed_at')
            ->where(fn ($q) => $q->whereNull('next_attempt_at')->orWhere('next_attempt_at', '<=', now()));

        if ($id = $this->option('update')) {
            if (! ctype_digit((string) $id)) {
                $this->error('Update ID must be a positive integer.');

                return self::FAILURE;
            }
            $query->where('update_id', $id);
            (clone $query)->update(['attempts' => 0]);
        }

        $ids = $query->where('attempts', '<', ProcessTelegramUpdate::MAX_ATTEMPTS)
            ->orderBy('received_at')->limit(100)->pluck('update_id');
        foreach ($ids as $id) {
            ProcessTelegramUpdate::dispatch((int) $id);
        }

        $this->info('Queued updates: '.$ids->count());

        return self::SUCCESS;
    }
}
