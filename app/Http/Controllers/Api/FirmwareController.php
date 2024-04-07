<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Firmware;

class FirmwareController extends Controller
{

    public function index()
    {
        $firmwares = Firmware::all();
        return response()->json($firmwares);
    }

    public function destroy(string $id)
    {
        $firmware = Firmware::findOrFail($id);
        $firmware->delete();
        return  response()->json(['message' => 'Прошивка успешно удалена'], 200);;
    }

    public function restore($id)
    {
        $firmware = Firmware::withTrashed()->find($id);
        if ($firmware) {
            $firmware->restore();
            return response()->json(['message' => 'Прошивка успешно восстановлена'], 200);
        } else {
            return response()->json(['error' => 'Прошивка не найдена'], 404);
        }
    }
}
