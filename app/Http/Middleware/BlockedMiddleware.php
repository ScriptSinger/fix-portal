<?php

namespace App\Http\Middleware;

use App\Models\Blocker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $blockedIp = Blocker::where('ip_address', $request->ip())->exists();
        if ($blockedIp) {
            abort(403);
        }
        return $next($request);
    }
}
