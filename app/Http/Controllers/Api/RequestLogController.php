<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class RequestLogController extends Controller
{

    public function index()
    {
        // Вычисляем дату 10 дней назад
        $startDate = Carbon::now()->subDays(10);

        // Выполняем запрос к базе данных для выборки данных за последние 10 дней
        $requestLogs = RequestLog::where('created_at', '>=', $startDate)->with('user')->get();

        Log::debug($requestLogs);
        return response()->json($requestLogs);
    }
}
