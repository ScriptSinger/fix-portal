<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Reply;
use Illuminate\Http\Request;

class ReplyController extends Controller
{
    public function store(Request $request, $id)
    {
        $data = $request->validate([
            'text' => 'required|string',
        ]);

        $data['user_id'] = auth()->user()->id;
        $comment = Comment::findOrFail($id);

        $reply = $comment->replies()->create($data);
        return redirect()->back();
    }

    public function destroy($id)
    {

        $reply = Reply::findOrFail($id);
        $this->authorize('delete', $reply);
        $reply->delete();

        return redirect()->back();
    }
}
