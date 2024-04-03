<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blocker;
use Illuminate\Http\Request;

class BlockedController extends Controller
{
    public function index()
    {
        $blockeds = Blocker::withTrashed()->get();
        return response()->json($blockeds);
    }

    public function destroy(string $id)
    {
        $blocked = Blocker::findOrFail($id);
        $blocked->delete();
        return  response()->json(['message' => 'IP-адрес успешно удалена'], 200);;
    }

    public function restore($id)
    {
        $blocked = Blocker::withTrashed()->find($id);
        if ($blocked) {
            $blocked->restore();
            return response()->json(['message' => 'IP-адрес успешно восстановлена'], 200);
        } else {
            return response()->json(['error' => 'IP-адрес не найдена'], 404);
        }
    }
}
