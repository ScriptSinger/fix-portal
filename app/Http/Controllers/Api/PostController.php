<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::withTrashed()->with('category', 'tags')->get();
        return response()->json($posts);
    }

    public function destroy(string $id)
    {
        $post = Post::findOrFail($id);
        $post->delete();
        return  response()->json(['message' => 'Статья успешно удалена'], 200);
    }

    public function restore($id)
    {
        $post = Post::withTrashed()->find($id);
        if ($post) {
            $post->restore();
            return response()->json(['message' => 'Статья успешно восстановлена'], 200);
        } else {
            return response()->json(['error' => 'Статья не найдена'], 404);
        }
    }
}
