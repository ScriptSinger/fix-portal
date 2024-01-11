<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Dislike;
use App\Models\Like;
use App\Services\LikeService;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    protected $likeService;

    public function __construct(LikeService $likeService)
    {
        $this->likeService = $likeService;
    }


    public function store(Request $request, $commentableType, $commentableId)
    {
        $this->authorize('create', [Comment::class, $commentableType]);
        $data = $request->validate([
            'text' => 'required|string',
        ]);
        $commentableModel = ("App\\Models\\" . ucfirst($commentableType))::findOrFail($commentableId);
        $data['user_id'] = auth()->user()->id;
        $comment = $commentableModel->comments()->create($data);

        return redirect()->back();
    }

    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('delete', $comment);
        $comment->delete();
        $comment->replies()->delete();
        return redirect()->back();
    }

    public function like($id)
    {
        $comment = Comment::findOrFail($id);
        $user = auth()->user();
        $this->likeService->toggleLike($comment, $user);
        $counter = $this->likeService->counter($comment);
        return response()->json($counter);
    }

    public function dislike($id)
    {
        $comment = Comment::findOrFail($id);
        $user = auth()->user();
        $this->likeService->toggleDisLike($comment, $user);
        $counter = $this->likeService->counter($comment);
        return response()->json($counter);
    }
}
