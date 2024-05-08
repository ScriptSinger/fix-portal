<?php

namespace App\Http\Middleware;

use App\Models\RequestLog;
use Closure;
use Dadata\DadataClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class LogRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Пропускаем запрос дальше в цепочку middleware и получаем ответ
        $response = $next($request);

        // Сохраняем данные запроса в базу данных
        $log = new RequestLog();
        $log->route = $request->path();
        $log->ipAddress = $request->ip();
        $log->userAgent = $request->header('User-Agent');
        $log->locale = $request->getLocale();
        $log->referer = $request->header('referer');
        $log->methodType = $request->method();
        $log->user_id = auth()->id(); // Получаем идентификатор аутентифицированного пользователя, если есть
        $log->status = $response->getStatusCode(); // Получаем статус ответа и сохраняем
        $log->save();

        return $response;
    }
}
