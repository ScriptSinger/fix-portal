<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Firmware;
use App\Services\FirmwareService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FirmwareController extends Controller
{

    public function index(Request $request)
    {

        $columns = ['id', 'title', 'size', 'date', 'extension', 'platform', 'crc32', 'data'];
        $query = DB::table('firmware');


        return response()->json(FirmwareService::simple($request, $query, $columns));
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
