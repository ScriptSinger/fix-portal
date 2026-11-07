<?php
namespace App\Telegram\Handlers;

use App\Actions\TelegramQuestions\AttachReply;
use App\Telegram\CommandResponse;
use SergiX44\Nutgram\Nutgram;

class AttachHandler
{
    public function __invoke(Nutgram $bot): void
    {
        $message = $bot->message()->toArray();
        $response = AttachReply::run(CommandResponse::arguments($message), $message, $bot->userId());
        app(CommandResponse::class)->send($message, $response, str_starts_with($response, 'Сообщение добавлено') || str_starts_with($response, 'Сообщение и вложения добавлены'));
    }
}
