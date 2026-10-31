<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\UpdateReplyRequest;
use App\Models\Comment;
use App\Models\Reply;
use App\Services\ActivityNotifier;
use App\Services\DiscussionMessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReplyController extends Controller
{
    public function __construct(
        private readonly ActivityNotifier $notifier,
        private readonly DiscussionMessageService $messages,
    )
    {
    }

    public function store(Request $request, Comment $comment)
    {
        $data = $request->validate([
            'text' => 'required_without:images|nullable|string|max:5000',
            'images' => 'nullable|array|max:6',
            'images.*' => 'string|max:255',
            'quote_reply_id' => 'nullable|integer',
        ]);

        $userId = auth()->user()->id;
        $text = $this->messages->makeText($data['text'] ?? null, $data['images'] ?? [], $userId);
        // цитировать можно только ответ из этой же ветки
        $quote = ! empty($data['quote_reply_id']) ? $comment->replies()->whereKey($data['quote_reply_id'])->value('id') : null;
        $reply = DB::transaction(fn () => $comment->replies()->create([
            'text' => $text,
            'user_id' => $userId,
            'quote_reply_id' => $quote,
        ]));
        $this->notifier->replied($reply, auth()->user());

        return redirect()->to($this->messages->url($reply))->withFragment('r-' . $reply->id);
    }

    public function update(UpdateReplyRequest $request, Reply $reply)
    {
        $this->authorize('update', $reply);

        $data = $request->validated();
        DB::transaction(fn () => $this->messages->update($reply, $data, auth()->id()));

        if ($request->expectsJson()) {
            return response()->json([
                'text' => $this->messages->plain($reply),
                'content' => $this->messages->rendered($reply),
                'id' => $reply->id,
            ]);
        }

        return redirect()->to($this->messages->url($reply))->withFragment('r-' . $reply->id);
    }

    public function destroy(Request $request, Reply $reply)
    {
        $this->authorize('delete', $reply);
        DB::transaction(fn () => $reply->delete());

        if ($request->expectsJson()) {
            return response()->json(['deleted' => true]);
        }

        return redirect()->back();
    }
}
