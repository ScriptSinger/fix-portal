<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Firmware;
use App\Models\Post;
use App\Models\Question;
use App\Models\Reply;
use App\Models\User;
use App\Notifications\ActivityNotification;
use App\Support\PlainText;
use Illuminate\Database\Eloquent\Model;

/**
 * Создаёт уведомления кабинета. Обсуждение везде одинаковое (вопросы, статьи, прошивки), поэтому события тоже.
 * Себе не пишем. «Ответили на ваш вопрос» и «решение» бывают только у вопросов: у статей и прошивок нет автора-пользователя.
 */
class ActivityNotifier
{
    public function __construct(private readonly ActivityMailer $mailer)
    {
    }

    public function answered(Comment $comment, User $actor): void
    {
        $subject = $comment->commentable;
        if (! $subject instanceof Question || ! $subject->user || $subject->user_id === $actor->id) {
            return;
        }

        $subject->user->notify(new ActivityNotification($this->payload(ActivityNotification::ANSWER, $actor, $subject, 'c-' . $comment->id, $comment->text)));
        $this->mailer->schedule($subject->user);
    }

    public function replied(Reply $reply, User $actor): void
    {
        $comment = $reply->comment;
        $subject = $comment?->commentable;
        if (! $subject || ! $this->describe($subject)) {
            return;
        }

        // кому отвечали: автору комментария и, если цитировали ответ, автору этого ответа
        $targets = collect([$comment->user]);
        if ($reply->quote_reply_id && ($quoted = Reply::with('user')->find($reply->quote_reply_id))) {
            $targets->push($quoted->user);
        }
        foreach ($targets->filter()->unique('id')->reject(fn (User $u) => $u->id === $actor->id) as $user) {
            $user->notify(new ActivityNotification($this->payload(ActivityNotification::REPLY, $actor, $subject, 'r-' . $reply->id, $reply->text)));
        }
    }

    public function reacted(Comment|Reply $message, User $actor, string $emoji): void
    {
        $subject = $message instanceof Comment ? $message->commentable : $message->comment?->commentable;
        if (! $subject || ! $this->describe($subject) || ! $message->user || $message->user_id === $actor->id) {
            return;
        }

        $anchor = ($message instanceof Comment ? 'c-' : 'r-') . $message->id;
        // одну и ту же реакцию того же человека на то же сообщение второй раз не показываем (снял и поставил снова)
        $again = $message->user->notifications()->where('data->kind', ActivityNotification::REACTION)
            ->where('data->actor_id', $actor->id)->where('data->target_id', $anchor)->where('data->emoji', $emoji)->exists();
        if ($again) {
            return;
        }

        $message->user->notify(new ActivityNotification($this->payload(ActivityNotification::REACTION, $actor, $subject, $anchor, $message->text, $emoji)));
    }

    /** Автору ответа: его ответ отмечен решением. */
    public function solution(Question $question, Comment|Reply $message, User $actor): void
    {
        if (! $message->user || $message->user_id === $actor->id) {
            return;
        }

        $anchor = ($message instanceof Comment ? 'c-' : 'r-') . $message->id;
        $message->user->notify(new ActivityNotification($this->payload(ActivityNotification::SOLUTION, $actor, $question, $anchor, $message->text)));
        $this->mailer->schedule($message->user);
    }

    /** Название, адрес и «где» (в вопросе, в статье, в прошивке) страницы, под которой написали. */
    private function describe(Model $subject): ?array
    {
        return match (true) {
            $subject instanceof Question => ['title' => $subject->title, 'url' => route('questions.show', ['question' => $subject->slug]), 'in' => 'вопросе'],
            $subject instanceof Post => ['title' => $subject->title, 'url' => route('articles.show', ['article' => $subject->slug]), 'in' => 'статье'],
            $subject instanceof Firmware => ['title' => $subject->title, 'url' => route('firmwares.show', ['firmware' => $subject->id]), 'in' => 'прошивке'],
            default => null,
        };
    }

    private function payload(string $kind, User $actor, Model $subject, string $anchor, ?string $text, ?string $emoji = null): array
    {
        $info = $this->describe($subject);
        return array_filter([
            'kind' => $kind,
            'actor_id' => $actor->id,
            'actor_name' => $actor->name,
            'title' => $info['title'],
            'in' => $info['in'],
            'url' => $info['url'] . '#' . $anchor,
            'target_id' => $anchor,
            'snippet' => PlainText::from($text, 140),
            'emoji' => $emoji,
        ], fn ($v) => $v !== null && $v !== '');
    }
}
