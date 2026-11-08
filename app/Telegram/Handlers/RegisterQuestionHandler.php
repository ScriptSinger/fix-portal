<?php

namespace App\Telegram\Handlers;

use App\Actions\TelegramQuestions\RegisterByModerator;
use App\Services\TelegramBotApi;
use App\Telegram\CommandResponse;
use App\Telegram\QuestionInstructions;
use SergiX44\Nutgram\Nutgram;

class RegisterQuestionHandler
{
    public function __invoke(Nutgram $bot): void
    {
        $message = $bot->message()->toArray();
        $result = RegisterByModerator::run($bot->update()->update_id, $message);
        if ($result !== 'registered') {
            app(CommandResponse::class)->send($message, $result);
            return;
        }
        $thread = (int) ($message['message_thread_id'] ?? 0);
        app(TelegramBotApi::class)->call('sendMessage', array_filter([
            'chat_id' => (int) data_get($message, 'chat.id'),
            'reply_to_message_id' => (int) data_get($message, 'reply_to_message.message_id'),
            'message_thread_id' => $thread > 1 ? $thread : null,
            'text' => QuestionInstructions::registered(),
        ], static fn ($value) => $value !== null));
    }
}
