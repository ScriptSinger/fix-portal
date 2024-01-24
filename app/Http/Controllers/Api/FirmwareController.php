<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Firmware;
use Illuminate\Http\Request;

class FirmwareController extends Controller
{

    public function index()
    {
        $firmwares = Firmware::all();
        return response()->json($firmwares);
    }
}
