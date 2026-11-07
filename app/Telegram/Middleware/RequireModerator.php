<?php
namespace App\Telegram\Middleware;

use App\Telegram\CommandResponse;
use SergiX44\Nutgram\Nutgram;

class RequireModerator
{
    public function __invoke(Nutgram $bot, $next): void
    {
        if (! $bot->userId() || ! in_array($bot->userId(), config('services.telegram.moderator_ids', []), true)) {
            app(CommandResponse::class)->send($bot->message()->toArray(), 'Нет доступа к командам модератора.');
            return;
        }
        $next($bot);
    }
}
