<?php

namespace App\Http\Middleware;

use App\Models\BlockedUserAgent;
use App\Models\Blocker;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class BlockedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $userAgent = $request->header('User-Agent');

        // Списки небольшие и меняются редко: держим их в кэше минуту вместо двух запросов в БД на каждый хит
        $blockedAgents = Cache::remember('blocked.user_agents', 60, fn () => BlockedUserAgent::query()
            ->pluck('user_agent')
            ->map(static fn (string $agent): string => Str::lower(trim($agent)))
            ->filter()
            ->values()
            ->all());

        $normalizedUserAgent = Str::lower(trim((string) $userAgent));
        foreach ($blockedAgents as $blockedAgent) {
            if ($normalizedUserAgent !== '' && Str::contains($normalizedUserAgent, $blockedAgent)) {
                abort(403);
            }
        }

        $blockedIps = Cache::remember('blocked.ips', 60, fn () => Blocker::pluck('ip_address')->all());
        if (in_array($request->ip(), $blockedIps, true)) {
            abort(403);
        }
        return $next($request);
    }
}
