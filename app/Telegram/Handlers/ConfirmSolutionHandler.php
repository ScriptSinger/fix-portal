<?php

namespace App\Telegram\Handlers;

use App\Services\TelegramBotApi;
use App\Services\TelegramReputation;
use Illuminate\Support\Facades\Cache;
use SergiX44\Nutgram\Nutgram;

class ConfirmSolutionHandler
{
    public function __invoke(Nutgram $bot): void
    {
        $callback = $bot->callbackQuery()->toArray();
        preg_match('/^solve:([a-f0-9]{24}):(\d+)$/', (string) ($callback['data'] ?? ''), $parts);
        $token = $parts[1];
        $id = (int) $parts[2];
        $message = Cache::get('telegram-solution:'.$token);
        $api = app(TelegramBotApi::class);
        if (! is_array($message)
            || (int) data_get($callback, 'from.id') !== (int) data_get($message, 'from.id')
            || (int) data_get($callback, 'message.chat.id') !== (int) data_get($message, 'chat.id')) {
            $api->call('answerCallbackQuery', ['callback_query_id' => $callback['id'], 'text' => 'Отметить решение может только автор вопроса.', 'show_alert' => true]);
            return;
        }
        $result = $id === 0 ? 'Вопрос остаётся открытым.' : app(TelegramReputation::class)->resolve($message, $id);
        $completed = $id === 0 || str_starts_with($result, 'Вопрос отмечен решённым.');
        if ($completed) {
            // SQL uniqueness also protects against callbacks from another prompt.
            Cache::forget('telegram-solution:'.$token);
            if ($id !== 0) {
                app(ReactionSolutionHandler::class)->acknowledge(
                    (int) data_get($message, 'chat.id'),
                    (int) data_get($message, 'reply_to_message.message_id'),
                );
            }
        }
        $api->call('answerCallbackQuery', [
            'callback_query_id' => $callback['id'],
            'text' => mb_strimwidth($result, 0, 190, '…'),
            'show_alert' => ! $completed,
        ]);
        if (! $completed) {
            return;
        }
        $api->call('editMessageText', [
            'chat_id' => data_get($callback, 'message.chat.id'),
            'message_id' => data_get($callback, 'message.message_id'),
            'text' => $result,
            'reply_markup' => json_encode(['inline_keyboard' => []], JSON_THROW_ON_ERROR),
        ]);
    }
}
