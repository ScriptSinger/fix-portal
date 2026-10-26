<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Помечает время последнего запроса вошедшего пользователя. Пишем не чаще раза в минуту,
 * чтобы каждый клик не превращался в UPDATE.
 */
class TrackUserActivity
{
    private const WRITE_EVERY_SECONDS = 60;

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();

        // Считаем только пользователей с подтверждённой почтой: остальные это боты и недозарегистрированные
        if ($user instanceof User
            && $user->hasVerifiedEmail()
            && (! $user->last_seen_at || $user->last_seen_at->diffInSeconds(now(), true) >= self::WRITE_EVERY_SECONDS)) {
            // Через DB, чтобы не трогать updated_at и не запускать события модели
            DB::table('users')->where('id', $user->getKey())->update(['last_seen_at' => now()]);
        }

        return $response;
    }
}
