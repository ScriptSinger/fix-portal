<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreCommentRequest;
use App\Models\Comment;
use App\Services\ActivityNotifier;
use App\Support\MessageBody;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // public function store(Request $request, $type, $id)
    // {
    //     $this->authorize('create', [Comment::class, $type]);
    //     $data = $request->validate([
    //         'text' => 'required|string',
    //     ]);

    //     $commentable = ("App\\Models\\" . ucfirst($type))::findOrFail($id);
    //     $data['user_id'] = auth()->user()->id;
    //     $commentable->comments()->create($data);
    //     return redirect()->back();
    // }
    public function store(StoreCommentRequest $request, $type, $id)
    {
        $data = $request->validated();
        $commentable = ("App\\Models\\" . ucfirst($type))::findOrFail($id);
        $userId = auth()->user()->id;
        $text = MessageBody::make($data['text'] ?? null, $data['images'] ?? [], $userId);
        abort_if($text === '', 422, 'Пустое сообщение');
        $comment = $commentable->comments()->create(['text' => $text, 'user_id' => $userId]);
        app(ActivityNotifier::class)->answered($comment, auth()->user());
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
}
