<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appliance;
use Illuminate\Http\Request;

class ApplianceController extends Controller
{
    public function index()
    {
        $appliances = Appliance::withTrashed()->get();
        return response()->json($appliances);
    }

    public function destroy(string $id)
    {
        $appliance = Appliance::findOrFail($id);
        $appliance->delete();
        return  response()->json(['message' => 'Прибор успешно удален'], 200);;
    }

    public function restore($id)
    {
        $appliance = Appliance::withTrashed()->find($id);
        if ($appliance) {
            $appliance->restore();
            return response()->json(['message' => 'Прибор успешно восстановлен'], 200);
        } else {
            return response()->json(['error' => 'Прибор не найден'], 404);
        }
    }
}
