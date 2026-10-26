<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Question;
use App\Models\Reply;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Support\Str;

/** Создаёт уведомления кабинета. Себе и о сообщениях не в вопросах не пишем. */
class ActivityNotifier
{
    public function answered(Comment $comment, User $actor): void
    {
        $question = $comment->commentable;
        if (! $question instanceof Question || ! $question->user || $question->user_id === $actor->id) {
            return;
        }

        $question->user->notify(new ActivityNotification($this->payload(ActivityNotification::ANSWER, $actor, $question, 'c-' . $comment->id, $comment->text)));
    }

    public function replied(Reply $reply, User $actor): void
    {
        $comment = $reply->comment;
        $question = $comment?->commentable;
        if (! $question instanceof Question) {
            return;
        }

        // кому отвечали: автору комментария и, если цитировали ответ, автору этого ответа
        $targets = collect([$comment->user]);
        if ($reply->quote_reply_id && ($quoted = Reply::with('user')->find($reply->quote_reply_id))) {
            $targets->push($quoted->user);
        }
        foreach ($targets->filter()->unique('id')->reject(fn (User $u) => $u->id === $actor->id) as $user) {
            $user->notify(new ActivityNotification($this->payload(ActivityNotification::REPLY, $actor, $question, 'r-' . $reply->id, $reply->text)));
        }
    }

    public function reacted(Comment|Reply $message, User $actor, string $emoji): void
    {
        $question = $message instanceof Comment ? $message->commentable : $message->comment?->commentable;
        if (! $question instanceof Question || ! $message->user || $message->user_id === $actor->id) {
            return;
        }

        $anchor = ($message instanceof Comment ? 'c-' : 'r-') . $message->id;
        // одну и ту же реакцию того же человека на то же сообщение второй раз не показываем (снял и поставил снова)
        $again = $message->user->notifications()->where('data->kind', ActivityNotification::REACTION)
            ->where('data->actor_id', $actor->id)->where('data->target_id', $anchor)->where('data->emoji', $emoji)->exists();
        if ($again) {
            return;
        }

        $message->user->notify(new ActivityNotification($this->payload(ActivityNotification::REACTION, $actor, $question, $anchor, $message->text, $emoji)));
    }

    /** Автору ответа: его ответ отмечен решением. */
    public function solution(Question $question, Comment|Reply $message, User $actor): void
    {
        if (! $message->user || $message->user_id === $actor->id) {
            return;
        }

        $anchor = ($message instanceof Comment ? 'c-' : 'r-') . $message->id;
        $message->user->notify(new ActivityNotification($this->payload(ActivityNotification::SOLUTION, $actor, $question, $anchor, $message->text)));
    }

    private function payload(string $kind, User $actor, Question $question, string $anchor, ?string $text, ?string $emoji = null): array
    {
        $plain = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8')));

        return array_filter([
            'kind' => $kind,
            'actor_id' => $actor->id,
            'actor_name' => $actor->name,
            'question_title' => $question->title,
            'url' => route('questions.show', ['question' => $question->slug]) . '#' . $anchor,
            'target_id' => $anchor,
            'snippet' => Str::limit($plain, 140),
            'emoji' => $emoji,
        ], fn ($v) => $v !== null && $v !== '');
    }
}
