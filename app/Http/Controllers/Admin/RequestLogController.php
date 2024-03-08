<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RequestLog;
use Illuminate\Http\Request;

class RequestLogController extends Controller
{
    public function index()
    {
        return view('admin.logs.index');
    }

    public function clear()
    {
        RequestLog::truncate();
        return redirect()->route('admin.logs.index')->with('success', ' Данные были очищены');
    }
}
