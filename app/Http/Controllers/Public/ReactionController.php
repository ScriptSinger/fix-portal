<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Reaction;
use App\Models\Reply;
use App\Services\ActivityNotifier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReactionController extends Controller
{
    private const REACTABLE = ['comment' => Comment::class, 'reply' => Reply::class];

    /** Ставит реакцию, меняет её на другую или снимает, если нажали ту же самую. */
    public function toggle(Request $request, string $type, int $id)
    {
        $class = self::REACTABLE[$type] ?? abort(404);
        $data = $request->validate(['emoji' => ['required', Rule::in(Reaction::EMOJI)]]);
        $target = $class::findOrFail($id);
        $key = ['user_id' => auth()->id(), 'reactable_type' => $class, 'reactable_id' => $target->id];

        $current = Reaction::where($key)->first();
        if ($current && $current->emoji === $data['emoji']) {
            $current->delete();
            $mine = null;
        } else {
            Reaction::updateOrCreate($key, ['emoji' => $data['emoji']]);
            $mine = $data['emoji'];
            app(ActivityNotifier::class)->reacted($target, auth()->user(), $data['emoji']);
        }

        $counts = Reaction::where(['reactable_type' => $class, 'reactable_id' => $target->id])
            ->selectRaw('emoji, count(*) as n')->groupBy('emoji')->pluck('n', 'emoji');

        return response()->json(['counts' => $counts, 'mine' => $mine]);
    }
}
