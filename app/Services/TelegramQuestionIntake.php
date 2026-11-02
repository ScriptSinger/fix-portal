<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class TelegramQuestionIntake
{
    /** @param array<string, mixed> $payload */
    public function store(int $updateId, array $payload): bool
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

        $text = trim((string) ($message['text'] ?? $message['caption'] ?? ''));
        if ($text === '') {
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
