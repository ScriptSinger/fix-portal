<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserModerationMiddleware
{
    private const MUTED_ROUTES = [
        'comments.store',
        'comments.replies.store',
        'questions.store',
        'reactions.toggle',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('web');
        if (! $user) {
            return $next($request);
        }

        if ($user->blocked_at !== null) {
            abort(403, 'Пользователь заблокирован.');
        }

        if ($user->muted_until?->isFuture() && in_array($request->route()?->getName(), self::MUTED_ROUTES, true)) {
            abort(403, 'Пользователь временно ограничен.');
        }

        return $next($request);
    }
}
