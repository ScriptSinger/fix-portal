<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Firmware;
use App\Services\DuplicateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class MainController extends Controller
{
    public function index(DuplicateService $duplicateService)
    {

        $firmwareLinks = Firmware::count();
        $directory = storage_path('app/public/firmwares');
        $files = File::files($directory);
        $firmwareFiles = count($files);

        // Получение пар дубликатов
        $firmware = new Firmware();
        $duplicateCount = count($duplicateService->getDuplicates($firmware));


        return view('admin.statistic.index', compact('firmwareLinks', 'firmwareFiles', 'duplicateCount'));
    }
}
