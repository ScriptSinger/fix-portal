<?php

namespace App\Telegram\Handlers;

use App\Services\TelegramBotApi;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OfferSolutionHandler
{
    public function handle(array $message): bool
    {
        $chatId = (int) data_get($message, 'chat.id');
        $authorId = (int) data_get($message, 'from.id');
        $reply = data_get($message, 'reply_to_message');
        $helperId = (int) data_get($reply, 'from.id');
        if ($chatId === 0 || $authorId === 0 || ! is_array($reply) || $helperId === 0
            || $helperId === $authorId || data_get($reply, 'from.is_bot') || isset($reply['sender_chat'])) {
            return false;
        }

        $threadId = (int) ($message['message_thread_id'] ?? 0);
        $threadId = $threadId > 1 ? $threadId : 0;
        $questions = DB::table('telegram_question_messages')
            ->where('telegram_chat_id', $chatId)
            ->where('telegram_thread_id', $threadId)
            ->where('telegram_user_id', $authorId)
            ->whereNotNull('registered_at')
            ->whereNull('archived_at')
            ->where('resolution_status', 'open')
            ->where('telegram_message_id', '<', (int) data_get($reply, 'message_id'))
            ->orderByDesc('id')->limit(10)->get(['id', 'text']);

        if ($questions->isEmpty()) {
            return false;
        }

        $token = bin2hex(random_bytes(12));
        Cache::put('telegram-solution:'.$token, $message, now()->addMinutes(30));
        $buttons = [];
        foreach ($questions as $question) {
            $title = trim(preg_split('/\R/u', (string) $question->text)[0] ?? 'Вопрос');
            $buttons[] = [[
                'text' => '✅ '.mb_strimwidth($title ?: 'Вопрос', 0, 46, '…'),
                'callback_data' => "solve:{$token}:{$question->id}",
            ]];
        }
        $buttons[] = [['text' => 'Не решено', 'callback_data' => "solve:{$token}:0"]];

        app(TelegramBotApi::class)->call('sendMessage', array_filter([
            'chat_id' => $chatId,
            'reply_to_message_id' => (int) ($message['message_id'] ?? 0),
            'message_thread_id' => $threadId > 1 ? $threadId : null,
            'text' => $questions->count() === 1
                ? 'Этот ответ помог решить ваш вопрос?'
                : 'Этот ответ помог? Выберите решённый вопрос:',
            'reply_markup' => json_encode(['inline_keyboard' => $buttons], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ], static fn ($value) => $value !== null));

        return true;
    }
}
