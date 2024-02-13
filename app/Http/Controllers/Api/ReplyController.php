<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Reply;
use Illuminate\Http\Request;

class ReplyController extends Controller
{
    public function index($id = null)
    {
        if ($id !== null) {
            $comment = Comment::with('user', 'replies')->withTrashed()->findOrFail($id);
            $replies = $comment->replies;
        } else {
            $replies = Reply::with('user', 'comment', 'likes')->withTrashed()->get();
        }
        return response()->json($replies);
    }

    public function destroy(string $id)
    {
        $reply = Reply::findOrFail($id);
        $reply->delete();
        return  response()->json(['message' => 'Ответ успешно удален'], 200);;
    }

    public function restore($id)
    {
        $reply = Reply::withTrashed()->find($id);
        if ($reply) {
            $reply->restore();
            return response()->json(['message' => 'Ответ успешно восстановлен'], 200);
        } else {
            return response()->json(['error' => 'Ответ не найден'], 404);
        }
    }
}
