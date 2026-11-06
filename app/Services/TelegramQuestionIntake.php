<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class TelegramQuestionIntake
{
    /** @param array<string, mixed> $payload */
    public function latestPendingContinuation(array $payload): ?object
    {
        $message = $payload['message'] ?? null;
        if (! is_array($message)) {
            return null;
        }

        $chatId = $message['chat']['id'] ?? null;
        $userId = $message['from']['id'] ?? null;
        if ($chatId === null || $userId === null) {
            return null;
        }

        $minutes = max(1, (int) config('services.telegram.question_continuation_minutes', 10));

        return DB::table('telegram_question_messages')
            ->where('telegram_chat_id', (int) $chatId)
            ->where('telegram_user_id', (int) $userId)
            ->where('status', 'pending_moderation')
            ->whereNotNull('text')
            ->where('text', '<>', '')
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->latest('id')
            ->first();
    }

    /** @param array<string, mixed> $payload */
    public function store(int $updateId, array $payload, bool $allowReply = false): bool
    {
        $message = $payload['message'] ?? null;
        if (! is_array($message)) {
            return false;
        }

        $chatId = $message['chat']['id'] ?? null;
        $messageId = $message['message_id'] ?? null;
        $sourceChatId = config('services.telegram.source_chat_id');
        if ($chatId === null || $messageId === null || $sourceChatId === null || (string) $chatId !== (string) $sourceChatId) {
            return false;
        }

        // Ответы и комментарии не создают новые вопросы. Для них будет отдельный сценарий.
        if (! $allowReply && isset($message['reply_to_message'])) {
            return false;
        }

        $sourceThreadId = (int) config('services.telegram.source_thread_id');
        if ($sourceThreadId > 1 && (int) ($message['message_thread_id'] ?? 0) !== $sourceThreadId) {
            return false;
        }

        $text = trim((string) ($message['text'] ?? $message['caption'] ?? ''));
        $hasPhoto = is_array($message['photo'] ?? null) && $message['photo'] !== [];
        if ($text === '' && ! $hasPhoto) {
            return false;
        }

        return DB::table('telegram_question_messages')->insertOrIgnore([
            'telegram_chat_id' => (int) $chatId,
            'telegram_message_id' => (int) $messageId,
            'telegram_update_id' => $updateId,
            'telegram_user_id' => isset($message['from']['id']) ? (int) $message['from']['id'] : null,
            'text' => $text,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            'status' => 'pending_moderation',
            'created_at' => now(),
            'updated_at' => now(),
        ]) === 1;
    }
}
