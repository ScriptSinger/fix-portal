<?php

namespace App\Telegram;

use App\Services\TelegramBotApi;

class CommandResponse
{
    public function __construct(private TelegramBotApi $telegram) {}

    public function send(array $message, string $text, bool $quiet = false): void
    {
        $chat = (int) data_get($message, 'chat.id');
        if ($quiet && config('services.telegram.silent_source_commands') && (string) $chat === (string) config('services.telegram.source_chat_id')) {
            try {
                $this->telegram->call('deleteMessage', ['chat_id' => $chat, 'message_id' => $message['message_id']]);
                return;
            } catch (\Throwable) {
                // Missing delete permissions must not lose feedback.
            }
        }
        $thread = (int) ($message['message_thread_id'] ?? 0);
        $this->telegram->call('sendMessage', array_filter([
            'chat_id' => $chat, 'text' => $text, 'disable_web_page_preview' => true,
            'message_thread_id' => $thread > 1 ? $thread : null,
        ], static fn ($value) => $value !== null));
    }

    public static function arguments(array $message): array
    {
        $parts = preg_split('/\s+/u', trim((string) ($message['text'] ?? ''))) ?: [];
        array_shift($parts);
        return $parts;
    }
}
