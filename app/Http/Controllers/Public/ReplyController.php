<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\UpdateReplyRequest;
use App\Models\Comment;
use App\Models\Firmware;
use App\Models\Post;
use App\Models\Question;
use App\Models\Reply;
use App\Services\ActivityNotifier;
use App\Support\MessageBody;
use Illuminate\Http\Request;

class ReplyController extends Controller
{
    public function __construct(private readonly ActivityNotifier $notifier)
    {
    }

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
        $reply = $comment->replies()->create(['text' => $text, 'user_id' => $userId, 'quote_reply_id' => $quote]);
        $this->notifier->replied($reply, auth()->user());

        return redirect()->to($this->discussionUrl($reply->comment->commentable))->withFragment('r-' . $reply->id);
    }

    public function update(UpdateReplyRequest $request, $id)
    {
        $reply = Reply::findOrFail($id);
        $this->authorize('update', $reply);

        $data = $request->validated();
        $text = MessageBody::make(
            $data['text'] ?? null,
            $data['images'] ?? [],
            auth()->id(),
            $this->existingImages($reply->text),
        );
        abort_if($text === '', 422, 'Пустое сообщение');

        $reply->update(['text' => $text]);

        if ($request->expectsJson()) {
            return response()->json(['text' => MessageBody::toPlain($reply->text), 'id' => $reply->id]);
        }

        return redirect()->to($this->discussionUrl($reply->comment->commentable))->withFragment('r-' . $reply->id);
    }

    /** @return list<string> */
    private function existingImages(?string $body): array
    {
        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', (string) $body, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }

    private function discussionUrl($subject): string
    {
        return match (class_basename($subject)) {
            'Post' => route('articles.show', ['article' => $subject->slug]),
            'Question' => route('questions.show', ['question' => $subject->slug]),
            'Firmware' => route('firmwares.show', ['firmware' => $subject->id]),
            default => url('/'),
        };
    }

    public function destroy(Request $request, $id)
    {

        $reply = Reply::findOrFail($id);
        $this->authorize('delete', $reply);
        $reply->delete();

        if ($request->expectsJson()) {
            return response()->json(['deleted' => true]);
        }

        return redirect()->back();
    }
}
