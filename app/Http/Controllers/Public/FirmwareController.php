<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Filters\Firmwares\DataFilter;

use App\Models\Firmware;
use Illuminate\Pipeline\Pipeline;

class FirmwareController extends Controller
{
    public function index()
    {
        $firmwares = app(Pipeline::class)
            ->send(Firmware::query())
            ->through([
                DataFilter::class
            ])
            ->thenReturn()
            // ->with('category', 'thumbnail')
            ->orderBy('id', 'desc')
            ->paginate(20);

        $platforms = Firmware::query()
            ->whereNotNull('platform')
            ->where('platform', '!=', '')
            ->distinct()
            ->orderBy('platform')
            ->pluck('platform');

        $extensions = Firmware::query()
            ->whereNotNull('extension')
            ->where('extension', '!=', '')
            ->distinct()
            ->orderBy('extension')
            ->pluck('extension');

        return view('public.firmwares.index', compact('firmwares', 'platforms', 'extensions'));
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
