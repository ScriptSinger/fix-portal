<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockedUserAgent;
use Illuminate\Http\Request;

class BlockedUserAgentController extends Controller
{
    public function index()
    {
        $blockeds = BlockedUserAgent::withTrashed()->get();
        return response()->json($blockeds);
    }


    public function destroy(string $id)
    {
        $blocked = BlockedUserAgent::findOrFail($id);
        $blocked->delete();
        return  response()->json(['message' => 'User-agent успешно удалена'], 200);;
    }

    public function restore($id)
    {
        $blocked = BlockedUserAgent::withTrashed()->find($id);
        if ($blocked) {
            $blocked->restore();
            return response()->json(['message' => 'User-agent успешно восстановлена'], 200);
        } else {
            return response()->json(['error' => 'User-agent не найдена'], 404);
        }
    }
}
