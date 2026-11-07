<?php
namespace App\Telegram\Middleware;

use App\Telegram\CommandResponse;
use Illuminate\Support\Facades\RateLimiter;
use SergiX44\Nutgram\Nutgram;

class ThrottleCommand
{
    public function __invoke(Nutgram $bot, $next): void
    {
        $command = explode('@', explode(' ', $bot->message()->text ?? '')[0])[0];
        $key = "telegram-command:{$bot->chatId()}:{$bot->userId()}:{$command}";
        $limit = max(1, (int) config('services.telegram.command_rate_limit_per_minute', 10));
        if (RateLimiter::tooManyAttempts($key, $limit)) {
            app(CommandResponse::class)->send($bot->message()->toArray(), 'Слишком много команд. Повторите через '.RateLimiter::availableIn($key).' сек.');
            return;
        }
        RateLimiter::hit($key, 60);
        $next($bot);
    }
}
