<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Filters\Duplicate;
use App\Http\Filters\ExtensionFilter;
use App\Http\Filters\PlatformFilter;
use App\Http\Filters\TitleFilter;
use App\Http\Sort\Firmwares\Crc32Sort;

use App\Http\Sort\Firmwares\IdSort;

use App\Http\Sort\Firmwares\SizeSort;

use App\Models\Firmware;

use App\Models\Path;
use App\Services\DuplicateService;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;


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
