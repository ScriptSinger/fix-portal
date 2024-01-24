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
    // public function index(Request $request)
    // {
    //     $firmwares = app()->make(Pipeline::class)
    //         ->send(Firmware::query())
    //         ->through([
    //             Duplicate::class,
    //             IdSort::class,
    //             // TitleFilter::class поправить
    //             SizeSort::class,
    //             ExtensionFilter::class,
    //             PlatformFilter::class,
    //             Crc32Sort::class


    //         ])
    //         ->thenReturn()
    //         ->paginate(50);

    //     return view('admin.firmwares.index', compact('firmwares',));
    // }

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

    public function downloadFile($filename)
    {
        $path = storage_path('app/public/firmwares/' . $filename);
        if (file_exists($path)) {
            return response()->download($path);
        } else {
            return redirect()->back()->with('error', 'Файл не найден');
        }
    }



    // -----



    public function markDuplicates()
    {
        // Получаем все прошивки
        $firmwares = Firmware::all();

        // Группируем их по полю 'crc32'
        $groupedFirmwares = $firmwares->groupBy('crc32');

        // Фильтруем группы, оставляем только те, в которых более одной записи
        $duplicates = $groupedFirmwares->filter(function ($group) {
            return $group->count() > 1;
        });


        // Получаем массив идентификаторов дубликатов
        $duplicateIds = $duplicates->flatten()->pluck('id')->toArray();
        // Массово обновляем записи, маркируя только дубликаты (кроме первой в группе)
        Firmware::whereIn('id', $duplicateIds)
            ->where('is_duplicate', false)
            ->update(['is_duplicate' => true]);

        return redirect()->route('admin.index')->with('success', 'Дубликаты промаркированы');
    }





    public function getDuplicates(DuplicateService $duplicateService)
    {

        $duplicatePairs = $duplicateService->getDuplicatePairs();

        return view('admin.firmwares.duplicates', compact('duplicatePairs'));
    }

    public function removeSecondFromAllDuplicates(DuplicateService $duplicateService)
    {
        $duplicatePairs = $duplicateService->getDuplicatePairs();
        $secondRecordIds = array_column(array_column($duplicatePairs, 'second'), 'id');
        Firmware::whereIn('id', $secondRecordIds)->delete();
        return redirect()->route('admin.index')->with('success', 'Дубликаты удалены');
    }
}
