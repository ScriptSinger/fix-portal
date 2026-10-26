<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Question;
use App\Models\Reaction;
use App\Models\Reply;
use Illuminate\Http\Request;

class CabinetController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $stats = [
            'unread' => $user->unreadNotifications()->count(),
            'questions' => $user->questions()->count(),
            'answers' => $this->answersCount($user->id),
            'solutions' => \App\Services\Reputation::countFor($user->id),
        ];
        $questions = $user->questions()->withCount('comments')->latest('id')->limit(5)->get();

        return view('public.cabinet.index', compact('stats', 'questions'));
    }

    public function notifications(Request $request)
    {
        $user = $request->user();
        $only = $request->query('show') === 'new';
        $query = $only ? $user->unreadNotifications() : $user->notifications();
        $notifications = $query->paginate(30)->withQueryString();

        return view('public.cabinet.notifications', compact('notifications', 'only'));
    }

    public function open(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        $url = $notification->data['url'] ?? route('cabinet.index');

        return redirect($url);
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back();
    }

    public function questions(Request $request)
    {
        $questions = $request->user()->questions()->with('appliance')->withCount('comments')->latest('id')->paginate(15);

        return view('public.cabinet.questions', compact('questions'));
    }

    public function answers(Request $request)
    {
        $id = $request->user()->id;
        $comments = Comment::where('user_id', $id)->where('commentable_type', Question::class)->with(['commentable', 'reactions'])->get()
            ->map(fn (Comment $c) => ['text' => $c->text, 'question' => $c->commentable, 'anchor' => 'c-' . $c->id, 'at' => $c->created_at, 'reactions' => $c->reactions]);
        $replies = Reply::where('user_id', $id)->whereHas('comment', fn ($q) => $q->where('commentable_type', Question::class))->with(['comment.commentable', 'reactions'])->get()
            ->map(fn (Reply $r) => ['text' => $r->text, 'question' => $r->comment->commentable, 'anchor' => 'r-' . $r->id, 'at' => $r->created_at, 'reactions' => $r->reactions]);
        $answers = $comments->concat($replies)->filter(fn ($a) => $a['question'])->sortByDesc('at')->take(50)->values();

        return view('public.cabinet.answers', compact('answers'));
    }

    private function answersCount(int $userId): int
    {
        return Comment::where('user_id', $userId)->where('commentable_type', Question::class)->count()
            + Reply::where('user_id', $userId)->whereHas('comment', fn ($q) => $q->where('commentable_type', Question::class))->count();
    }

}
