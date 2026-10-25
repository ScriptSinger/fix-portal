<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Reply;
use App\Support\MessageBody;
use Illuminate\Http\Request;

class ReplyController extends Controller
{
    public function store(Request $request, $id)
    {
        $data = $request->validate([
            'text' => 'required_without:images|nullable|string|max:5000',
            'images' => 'nullable|array|max:6',
            'images.*' => 'string|max:255',
            'quote_reply_id' => 'nullable|integer',
        ]);

        $userId = auth()->user()->id;
        $text = MessageBody::make($data['text'] ?? null, $data['images'] ?? [], $userId);
        abort_if($text === '', 422, 'Пустое сообщение');
        $comment = Comment::findOrFail($id);

        // цитировать можно только ответ из этой же ветки
        $quote = ! empty($data['quote_reply_id']) ? $comment->replies()->whereKey($data['quote_reply_id'])->value('id') : null;
        $comment->replies()->create(['text' => $text, 'user_id' => $userId, 'quote_reply_id' => $quote]);
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
