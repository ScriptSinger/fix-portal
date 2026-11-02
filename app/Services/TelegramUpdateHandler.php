<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TelegramUpdateHandler
{
    public function __construct(private readonly TelegramBotApi $telegram)
    {
    }

    /** @param array<string, mixed> $payload */
    public function handle(int $updateId, array $payload): void
    {
        $message = $payload['message'] ?? null;
        if (! is_array($message) || ! is_array($message['chat'] ?? null)) {
            $this->markProcessed($updateId);
            return;
        }

        $text = trim((string) ($message['text'] ?? ''));
        $chatId = $message['chat']['id'] ?? null;
        if ($text === '' || $chatId === null) {
            $this->markProcessed($updateId);
            return;
        }

        $command = $this->command($text);
        if ($command === null) {
            $this->markProcessed($updateId);
            return;
        }

        $telegramUserId = (int) ($message['from']['id'] ?? 0);
        $response = $this->isModerator($telegramUserId)
            ? $this->moderatorResponse($command)
            : 'Нет доступа к командам модератора.';

        $this->telegram->call('sendMessage', [
            'chat_id' => $chatId,
            'text' => $response,
            'disable_web_page_preview' => true,
        ]);

        $this->markProcessed($updateId);
    }

    private function command(string $text): ?string
    {
        $firstWord = Str::before($text, ' ');
        $command = Str::lower(Str::before($firstWord, '@'));

        return in_array($command, ['/start', '/help', '/status'], true) ? $command : null;
    }

    private function isModerator(int $telegramUserId): bool
    {
        return $telegramUserId > 0 && in_array($telegramUserId, config('services.telegram.moderator_ids', []), true);
    }

    private function moderatorResponse(string $command): string
    {
        return match ($command) {
            '/start' => 'Telegram-модератор подключён. Используйте /help для списка команд.',
            '/help' => "/start — состояние подключения\n/help — список команд\n/status — состояние webhook",
            '/status' => 'Webhook активен. Обновления принимаются и сохраняются в Laravel.',
        };
    }

    private function markProcessed(int $updateId): void
    {
        DB::table('telegram_webhook_updates')->where('update_id', $updateId)->update(['processed_at' => now()]);
    }
}
