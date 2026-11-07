<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class TelegramQuestionIntake
{
    /** @param array<string, mixed> $payload */
    public function pendingReplyContinuation(array $payload): ?object
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

        $replyMessageId = (int) data_get($message, 'reply_to_message.message_id');
        if ($replyMessageId < 1) {
            return null;
        }

        $records = DB::table('telegram_question_messages')
            ->where('telegram_chat_id', (int) $chatId)
            ->where('telegram_user_id', (int) $userId)
            ->where('status', 'pending_moderation')
            ->whereNotNull('text')
            ->where('text', '<>', '')
            ->latest('id')
            ->get();

        foreach ($records as $record) {
            if ((int) $record->telegram_message_id === $replyMessageId) {
                return $record;
            }

            $payload = json_decode((string) $record->payload, true) ?: [];
            $attachedIds = array_map('intval', (array) ($payload['attached_message_ids'] ?? []));
            if (in_array($replyMessageId, $attachedIds, true)) {
                return $record;
            }
        }

        return null;
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
        if ($text === '' && (! $allowReply || ! $hasPhoto)) {
            return false;
        }

        // Автоматически считаем сообщением-вопросом только текст с вопросительным знаком.
        // Команда /question передаёт allowReply=true и может явно пометить любой текст/фото.
        if (! $allowReply && ! str_contains($text, '?') && ! str_contains($text, '？')) {
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
