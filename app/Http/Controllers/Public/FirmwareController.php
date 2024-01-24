<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Filters\Firmware\TextFilter;
use App\Models\Firmware;
use Illuminate\Pipeline\Pipeline;

class FirmwareController extends Controller
{
    public function index()
    {
        $firmwares = app()->make(Pipeline::class)
            ->send(Firmware::query())
            ->through([
                TextFilter::class

            ])
            ->thenReturn()
            ->paginate(50);

        return view('public.firmwares.index', compact('firmwares'));
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
