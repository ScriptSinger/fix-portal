<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Question;
use App\Models\Reply;
use App\Services\ActivityNotifier;
use Illuminate\Http\Request;

class SolutionController extends Controller
{
    /** Автор отмечает вопрос решённым, при желании указывает ответ-решение. Можно сменить решение. */
    public function store(Request $request, int $id)
    {
        $question = Question::findOrFail($id);
        abort_unless($question->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'type' => ['nullable', 'in:comment,reply'],
            'message' => ['nullable', 'integer'],
        ]);

        $message = null;
        if (! empty($data['type']) && ! empty($data['message'])) {
            $message = $data['type'] === 'comment'
                ? Comment::where('commentable_type', Question::class)->where('commentable_id', $question->id)->findOrFail($data['message'])
                : Reply::whereHas('comment', fn ($q) => $q->where('commentable_type', Question::class)->where('commentable_id', $question->id))->findOrFail($data['message']);
        }

        $changed = $message && ($question->solution_type !== $data['type'] || (int) $question->solution_id !== $message->id);
        $question->forceFill([
            'solved_at' => $question->solved_at ?? now(),
            'solution_type' => $message ? $data['type'] : null,
            'solution_id' => $message?->id,
        ])->save();

        if ($changed) {
            app(ActivityNotifier::class)->solution($question, $message, $request->user());
        }

        return redirect(route('questions.show', ['question' => $question->slug]) . ($message ? '#' . ($data['type'] === 'comment' ? 'c-' : 'r-') . $message->id : ''));
    }

    public function destroy(Request $request, int $id)
    {
        $question = Question::findOrFail($id);
        abort_unless($question->user_id === $request->user()->id, 403);
        $question->forceFill(['solved_at' => null, 'solution_type' => null, 'solution_id' => null])->save();

        return redirect(route('questions.show', ['question' => $question->slug]));
    }
}
