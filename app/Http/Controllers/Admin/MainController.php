<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Firmware;
use App\Services\DuplicateService;

class MainController extends Controller
{
    public function index(DuplicateService $duplicateService)
    {
        // $firmware = new Firmware();
        // $duplicateCount = count($duplicateService->getDuplicates($firmware));
        return view('admin.statistics.index');
    }
}
