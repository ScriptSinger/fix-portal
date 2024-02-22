<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Firmware;

class FirmwareController extends Controller
{
    public function index()
    {
        return view('public.firmwares.index');
    }

    public function show(string $slug)
    {
        $firmware = Firmware::findOrFail($slug);
        return view('public.firmwares.show', compact('firmware'));
    }

    public function download($filename)
    {
        $path = storage_path('app/public/firmwares/' . $filename);
        if (file_exists($path)) {
            return response()->download($path);
        } else {
            return redirect()->back()->with('error', 'Файл не найден');
        }
    }
}
