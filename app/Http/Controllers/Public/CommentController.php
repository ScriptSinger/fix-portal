<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreCommentRequest;
use App\Http\Requests\Public\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\Firmware;
use App\Models\Post;
use App\Models\Question;
use App\Services\ActivityNotifier;
use App\Support\MessageBody;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(private readonly ActivityNotifier $notifier)
    {
    }

    public function store(StoreCommentRequest $request, $type, $id)
    {
        $data = $request->validated();
        $commentable = ("App\\Models\\" . ucfirst($type))::findOrFail($id);
        $userId = auth()->user()->id;
        $text = MessageBody::make($data['text'] ?? null, $data['images'] ?? [], $userId);
        abort_if($text === '', 422, 'Пустое сообщение');
        $comment = $commentable->comments()->create(['text' => $text, 'user_id' => $userId]);
        $this->notifier->answered($comment, auth()->user());

        // возвращаем к новому сообщению, а не в начало страницы
        return redirect()->to($this->discussionUrl($comment->commentable))->withFragment('c-' . $comment->id);
    }

    public function destroy(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('delete', $comment);
        $comment->delete();
        $comment->replies()->delete();
        if ($request->expectsJson()) {
            return response()->json(['deleted' => true]);
        }
        return redirect()->back();
    }

    public function update(UpdateCommentRequest $request, $id)
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('update', $comment);

        $data = $request->validated();
        $text = MessageBody::make(
            $data['text'] ?? null,
            $data['images'] ?? [],
            auth()->id(),
            $this->existingImages($comment->text),
        );
        abort_if($text === '', 422, 'Пустое сообщение');

        $comment->update(['text' => $text]);

        if ($request->expectsJson()) {
            return response()->json(['text' => MessageBody::toPlain($comment->text), 'id' => $comment->id]);
        }

        return redirect()->to($this->discussionUrl($comment->commentable))->withFragment('c-' . $comment->id);
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
}
