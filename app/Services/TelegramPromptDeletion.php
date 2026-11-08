<?php

namespace App\Services;

class TelegramPromptDeletion
{
    public function __construct(private readonly TelegramBotApi $telegram) {}

    public function delete(int $chatId, int $messageId): void
    {
        try {
            $this->telegram->call('deleteMessage', ['chat_id' => $chatId, 'message_id' => $messageId]);
        } catch (\RuntimeException $exception) {
            if (str_contains(mb_strtolower($exception->getMessage()), 'message to delete not found')) {
                return;
            }
            throw $exception;
        }
    }
}
