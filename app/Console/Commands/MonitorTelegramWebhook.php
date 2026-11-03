<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MonitorTelegramWebhook extends Command
{
    protected $signature = 'telegram:webhook-monitor
                            {--minutes=5 : Age in minutes after which an unprocessed update is stale}
                            {--json : Print machine-readable output}';

    protected $description = 'Find stale and failed Telegram webhook updates';

    public function handle(): int
    {
        $minutes = max(1, (int) $this->option('minutes'));
        $cutoff = now()->subMinutes($minutes);

        $query = DB::table('telegram_webhook_updates')
            ->whereNull('processed_at')
            ->where('received_at', '<', $cutoff);

        $stale = $query->count();
        $failed = (clone $query)->whereNotNull('failed_at')->count();
        $latest = (clone $query)
            ->orderByDesc('received_at')
            ->limit(20)
            ->get(['update_id', 'received_at', 'attempts', 'failed_at', 'processing_error'])
            ->map(static fn (object $row): array => [
                'update_id' => (int) $row->update_id,
                'received_at' => (string) $row->received_at,
                'attempts' => (int) $row->attempts,
                'failed_at' => $row->failed_at,
                'error' => $row->processing_error,
            ])
            ->values()
            ->all();

        $result = [
            'stale_count' => $stale,
            'failed_count' => $failed,
            'cutoff' => $cutoff->toIso8601String(),
            'updates' => $latest,
        ];

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        } elseif ($stale === 0) {
            $this->info('Telegram webhook updates are healthy.');
        } else {
            $this->error("Found {$stale} stale Telegram webhook update(s), {$failed} failed.");
            foreach ($latest as $row) {
                $this->line(sprintf(
                    '#%d attempts=%d received=%s error=%s',
                    $row['update_id'],
                    $row['attempts'],
                    $row['received_at'],
                    $row['error'] ?: 'not recorded',
                ));
            }
        }

        if ($stale > 0) {
            Log::warning('Stale Telegram webhook updates detected.', $result);

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
