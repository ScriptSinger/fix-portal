<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TelegramUpdateHandler
{
    public function __construct(
        private readonly TelegramBotApi $telegram,
        private readonly TelegramModerationService $moderation,
        private readonly TelegramQuestionIntake $questionIntake,
        private readonly TelegramQuestionModerationService $questionModeration,
    )
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

        $parsed = $this->command($text);
        if ($parsed === null) {
            if ($this->questionIntake->store($updateId, $payload)) {
                $this->questionModeration->preview($updateId, $payload);
            }
            $this->markProcessed($updateId);
            return;
        }

        $telegramUserId = (int) ($message['from']['id'] ?? 0);
        $response = $this->isModerator($telegramUserId)
            ? $this->moderatorResponse($parsed[0], $parsed[1], $telegramUserId)
            : 'Нет доступа к командам модератора.';

        $this->telegram->call('sendMessage', [
            'chat_id' => $chatId,
            'text' => $response,
            'disable_web_page_preview' => true,
        ]);

        $this->markProcessed($updateId);
    }

    /** @return array{0: string, 1: list<string>}|null */
    private function command(string $text): ?array
    {
        $parts = preg_split('/\s+/u', trim($text)) ?: [];
        $firstWord = (string) array_shift($parts);
        $command = Str::lower(Str::before($firstWord, '@'));

        return in_array($command, ['/start', '/help', '/status', '/mute', '/unmute', '/block', '/unblock'], true)
            ? [$command, array_values($parts)]
            : null;
    }

    private function isModerator(int $telegramUserId): bool
    {
        return $telegramUserId > 0 && in_array($telegramUserId, config('services.telegram.moderator_ids', []), true);
    }

    /** @param list<string> $arguments */
    private function moderatorResponse(string $command, array $arguments, int $moderatorTelegramId): string
    {
        return match ($command) {
            '/start' => 'Telegram-модератор подключён. Используйте /help для списка команд.',
            '/help' => "/start — состояние подключения\n/help — список команд\n/status — состояние webhook\n/mute <user_id> [минуты]\n/unmute <user_id>\n/block <user_id>\n/unblock <user_id>",
            '/status' => 'Webhook активен. Обновления принимаются и сохраняются в Laravel.',
            '/mute', '/unmute', '/block', '/unblock' => $this->moderation->execute($command, $arguments, $moderatorTelegramId),
        };
    }

    private function markProcessed(int $updateId): void
    {
        DB::table('telegram_webhook_updates')->where('update_id', $updateId)->update(['processed_at' => now()]);
    }
}
