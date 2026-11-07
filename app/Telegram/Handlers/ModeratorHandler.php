<?php
namespace App\Telegram\Handlers;

use App\Services\TelegramModerationService;
use App\Telegram\CommandResponse;
use SergiX44\Nutgram\Nutgram;

class ModeratorHandler
{
    public function __invoke(Nutgram $bot): void
    {
        $message = $bot->message()->toArray();
        $command = explode('@', preg_split('/\s+/', trim($message['text']))[0])[0];
        $response = match ($command) {
            '/start' => 'Telegram-бот подключён. Используйте /help для списка команд.',
            '/help' => "/question — зарегистрировать вопрос ответом на сообщение\n/attach [ID] — прикрепить дополнение\n/solved [ID] — отметить решение ответом на сообщение помощника\n/status — состояние webhook\n/mute <user_id> [минуты]\n/unmute <user_id>\n/block <user_id>\n/unblock <user_id>",
            '/status' => 'Webhook активен. Обновления принимаются и сохраняются в Laravel.',
            default => app(TelegramModerationService::class)->execute($command, CommandResponse::arguments($message), $bot->userId()),
        };
        app(CommandResponse::class)->send($message, $response);
    }
}
