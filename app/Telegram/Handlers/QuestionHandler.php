<?php
namespace App\Telegram\Handlers;

use App\Actions\TelegramQuestions\MarkQuestion;
use App\Telegram\CommandResponse;
use App\Telegram\QuestionInstructions;
use App\Services\TelegramBotApi;
use SergiX44\Nutgram\Nutgram;

class QuestionHandler
{
    public function __invoke(Nutgram $bot): void
    {
        $message = $bot->message()->toArray();
        $response = MarkQuestion::run($bot->update()->update_id, $message);
        if ($response === 'Сообщение передано на модерацию.') {
            $chatId = (int) data_get($message, 'chat.id');
            $threadId = (int) ($message['message_thread_id'] ?? 0);
            if (config('services.telegram.silent_source_commands')) {
                try {
                    app(TelegramBotApi::class)->call('deleteMessage', [
                        'chat_id' => $chatId, 'message_id' => (int) $message['message_id'],
                    ]);
                } catch (\Throwable) {
                    // The author still receives the instructions if deletion is unavailable.
                }
            }
            app(TelegramBotApi::class)->call('sendMessage', array_filter([
                'chat_id' => $chatId,
                'reply_to_message_id' => (int) data_get($message, 'reply_to_message.message_id'),
                'message_thread_id' => $threadId > 1 ? $threadId : null,
                'text' => QuestionInstructions::registered(),
            ], static fn ($value) => $value !== null));
            return;
        }
        app(CommandResponse::class)->send($message, $response, $response === 'Сообщение передано на модерацию.');
    }
}
