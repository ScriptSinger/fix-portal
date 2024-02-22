<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Firmware;

class FirmwareController extends Controller
{

    public function index()
    {
        $firmwares = Firmware::all();
        return view('admin.firmwares.index', compact('firmwares'));
    }

    public function show(string $id)
    {
        $firmware = Firmware::findOrFail($id);
        return view('admin.firmwares.show', compact('firmware'));
    }

    public function edit(string $id)
    {
        $firmware = Firmware::findOrFail($id);
        return view('admin.firmwares.edit', compact('firmware'));
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
