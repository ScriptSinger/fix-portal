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
use App\Services\DiscussionMessageService;
use App\Services\LatestDiscussion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    /** @var array<string, class-string<Post|Question|Firmware>> */
    private const COMMENTABLES = [
        'post' => Post::class,
        'question' => Question::class,
        'firmware' => Firmware::class,
    ];

    public function __construct(
        private readonly ActivityNotifier $notifier,
        private readonly DiscussionMessageService $messages,
        private readonly LatestDiscussion $latestDiscussion,
    ) {}

    public function store(StoreCommentRequest $request, $type, $id)
    {
        $data = $request->validated();
        $model = self::COMMENTABLES[strtolower($type)] ?? abort(404);
        $commentable = $model::findOrFail($id);
        $userId = auth()->user()->id;
        $text = $this->messages->makeText($data['text'] ?? null, $data['images'] ?? [], $userId);
        $comment = DB::transaction(fn () => $commentable->comments()->create([
            'text' => $text,
            'user_id' => $userId,
        ]));
        $this->latestDiscussion->forget();
        $this->notifier->answered($comment, auth()->user());

        // возвращаем к новому сообщению, а не в начало страницы
        return redirect()->to($this->messages->url($comment))->withFragment('c-'.$comment->id);
    }

    public function destroy(Request $request, Comment $comment)
    {
        $this->authorize('delete', $comment);
        DB::transaction(fn () => $comment->delete());
        $this->latestDiscussion->forget();
        if ($request->expectsJson()) {
            return response()->json(['deleted' => true]);
        }

        return redirect()->back();
    }

    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $data = $request->validated();
        DB::transaction(fn () => $this->messages->update($comment, $data, auth()->id()));
        $this->latestDiscussion->forget();

        if ($request->expectsJson()) {
            return response()->json([
                'text' => $this->messages->plain($comment),
                'content' => $this->messages->rendered($comment),
                'id' => $comment->id,
            ]);
        }

        return redirect()->to($this->messages->url($comment))->withFragment('c-'.$comment->id);
    }
}
