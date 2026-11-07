<?php
namespace App\Telegram\Handlers;

use App\Services\TelegramReputation;
use App\Telegram\CommandResponse;
use SergiX44\Nutgram\Nutgram;

class SolvedHandler
{
    public function __invoke(Nutgram $bot): void
    {
        $message = $bot->message()->toArray();
        $args = CommandResponse::arguments($message);
        if (count($args) > 1 || (isset($args[0]) && (! ctype_digit($args[0]) || (int) $args[0] < 1))) {
            app(CommandResponse::class)->send($message, 'Используйте /solved [ID вопроса] ответом на сообщение помощника.');
            return;
        }
        $response = app(TelegramReputation::class)->resolve($message, isset($args[0]) ? (int) $args[0] : null);
        app(CommandResponse::class)->send($message, $response);
    }
}
