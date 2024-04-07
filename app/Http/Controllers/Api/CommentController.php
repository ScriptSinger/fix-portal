<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index()
    {
        $comments = Comment::with('user', 'commentable', 'replies', 'likes', 'dislikes')->withTrashed()->get();
        return response()->json($comments);
    }


    public function destroy(string $id)
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();
        return  response()->json(['message' => 'Комментарий успешно удален'], 200);;
    }

    public function restore($id)
    {
        $comment = Comment::withTrashed()->find($id);
        if ($comment) {
            $comment->restore();
            return response()->json(['message' => 'Комментарий успешно восстановлен'], 200);
        } else {
            return response()->json(['error' => 'Комментарий не найден'], 404);
        }
    }
}
