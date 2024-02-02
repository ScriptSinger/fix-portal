<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::withTrashed()->get();
        return response()->json($tags);
    }

    public function destroy(string $id)
    {
        $tag = Tag::findOrFail($id);
        $tag->delete();
        return  response()->json(['message' => 'Метка успешно удалена'], 200);;
    }

    public function restore($id)
    {
        $tag = Tag::withTrashed()->find($id);
        if ($tag) {
            $tag->restore();
            return response()->json(['message' => 'Метка успешно восстановлена'], 200);
        } else {
            return response()->json(['error' => 'Метка не найдена'], 404);
        }
    }
}
