<?php

namespace App\Services;

use App\Telegram\QuestionInstructions;
use Illuminate\Support\Facades\DB;

class TelegramRegistrationPrompt
{
    public function __construct(private readonly TelegramBotApi $telegram) {}

    public function confirm(int $questionId, int $chatId, int $messageId): void
    {
        try {
            $this->telegram->call('editMessageText', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => QuestionInstructions::registered(),
                'reply_markup' => json_encode(['inline_keyboard' => []], JSON_THROW_ON_ERROR),
            ]);
        } catch (\RuntimeException $exception) {
            // Telegram may have accepted an earlier attempt whose response was lost.
            if (! str_contains(mb_strtolower($exception->getMessage()), 'message is not modified')) {
                throw $exception;
            }
        }

        DB::table('telegram_question_messages')->where('id', $questionId)
            ->where('confirmation_prompt_message_id', $messageId)
            ->update(['confirmation_prompt_message_id' => null, 'updated_at' => now()]);
    }
}
