<?php

namespace App\Console\Commands;

use App\Services\TelegramBotApi;
use Illuminate\Console\Command;

class TelegramWebhook extends Command
{
    protected $signature = 'telegram:webhook
                            {action : set, info or delete}
                            {--drop-pending-updates : Drop pending updates when setting or deleting the webhook}';

    protected $description = 'Manage the Telegram webhook through the configured proxy';

    public function handle(TelegramBotApi $telegram): int
    {
        try {
            $result = match ($this->argument('action')) {
                'set' => $this->set($telegram),
                'info' => $this->showInfo($telegram),
                'delete' => $this->delete($telegram),
                default => $this->invalidAction(),
            };
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        return $result;
    }

    private function set(TelegramBotApi $telegram): int
    {
        $url = (string) config('services.telegram.webhook_url');
        $secret = (string) config('services.telegram.webhook_secret');

        if ($url === '' || $secret === '') {
            $this->error('TELEGRAM_WEBHOOK_URL and TELEGRAM_WEBHOOK_SECRET are required.');

            return self::FAILURE;
        }

        $telegram->call('setWebhook', array_filter([
            'url' => $url,
            'secret_token' => $secret,
            'allowed_updates' => ['message', 'my_chat_member'],
            'drop_pending_updates' => $this->option('drop-pending-updates') ?: null,
        ], static fn ($value) => $value !== null));

        $this->info('Telegram webhook registered: '.$url);

        return self::SUCCESS;
    }

    private function showInfo(TelegramBotApi $telegram): int
    {
        $result = $telegram->call('getWebhookInfo');
        $this->line(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        return self::SUCCESS;
    }

    private function delete(TelegramBotApi $telegram): int
    {
        $telegram->call('deleteWebhook', array_filter([
            'drop_pending_updates' => $this->option('drop-pending-updates') ?: null,
        ], static fn ($value) => $value !== null));

        $this->info('Telegram webhook deleted.');

        return self::SUCCESS;
    }

    private function invalidAction(): int
    {
        $this->error('Action must be one of: set, info, delete.');

        return self::FAILURE;
    }
}
