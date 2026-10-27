<?php

namespace App\Support\Discussion;

use App\Models\Comment;
use App\Models\Question;
use App\Models\Reply;
use App\Services\Reputation;
use App\Support\ContentImages;
use App\Support\HtmlCleanup;
use App\Support\PlainText;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Лента обсуждения под вопросом, статьёй или прошивкой: комментарии и ответы одной лентой по времени.
 * Собирает данные для шаблона (серии сообщений, цитаты, решение, репутация), сам шаблон ничего не считает.
 * Все сущности работают одинаково; решения и «автор» бывают только у вопросов.
 */
final class Feed
{
    /** Число цветов имён участников */
    public const COLORS = 5;

    private ?Collection $entries = null;

    public function __construct(private readonly Model $subject, private readonly ?int $viewerId = null)
    {
    }

    public static function for(Model $subject, ?int $viewerId = null): self
    {
        return new self($subject, $viewerId);
    }

    public function isQuestion(): bool
    {
        return $this->subject instanceof Question;
    }

    /** Тексты: у вопроса «ответы», у остальных «комментарии». */
    public function words(): array
    {
        return $this->isQuestion()
            ? ['title' => 'Ответы', 'empty' => 'Пока нет ответов, напишите первым', 'placeholder' => 'Написать ответ', 'login' => 'Чтобы ответить']
            : ['title' => 'Комментарии', 'empty' => 'Пока нет комментариев, напишите первым', 'placeholder' => 'Написать комментарий', 'login' => 'Чтобы написать комментарий'];
    }

    public function storeUrl(): string
    {
        return route('comments.store', ['type' => strtolower(class_basename($this->subject)), 'id' => $this->subject->id]);
    }

    public function subjectId(): int
    {
        return $this->subject->id;
    }

    public function total(): int
    {
        return $this->entries()->count();
    }

    public function people(): int
    {
        return $this->entries()->map(fn (FeedEntry $e) => $e->message->user_id)->unique()->count();
    }

    /** Ответ, отмеченный решением: для закреплённой карточки над лентой. */
    public function solution(): ?array
    {
        $message = $this->isQuestion() ? $this->subject->solution() : null;
        if (! $message) {
            return null;
        }

        return [
            'anchor' => ($this->subject->solution_type === 'comment' ? 'c-' : 'r-') . $message->id,
            'author' => $message->user->name,
            'text' => PlainText::from($message->text, 200) ?: 'Фото',
        ];
    }

    /** @return Collection<int, FeedEntry> */
    public function entries(): Collection
    {
        return $this->entries ??= $this->build();
    }

    private function build(): Collection
    {
        $raw = collect();
        foreach ($this->subject->comments as $comment) {
            $raw->push(['message' => $comment, 'root' => $comment->id, 'quoted' => null]);
            foreach ($comment->replies as $reply) {
                // цитата: конкретный ответ, если отвечали на него, иначе сам комментарий
                $quoted = $reply->quote_reply_id ? $comment->replies->firstWhere('id', $reply->quote_reply_id) : null;
                $raw->push(['message' => $reply, 'root' => $comment->id, 'quoted' => $quoted ?? $comment]);
            }
        }
        $raw = $raw->sortBy(fn (array $r) => $r['message']->created_at)->values();

        $solutions = Reputation::counts($raw->map(fn (array $r) => $r['message']->user_id)->unique()->values()->all());

        return $raw->map(function (array $r, int $i) use ($raw, $solutions) {
            /** @var Comment|Reply $m */
            $m = $r['message'];
            $prev = $raw[$i - 1]['message'] ?? null;
            $next = $raw[$i + 1]['message'] ?? null;
            $type = $m instanceof Comment ? 'comment' : 'reply';
            [$body, $photos] = ContentImages::extract($m->text);
            $body = HtmlCleanup::stripInlineStyles($body);
            $isSolution = $this->isQuestion() && $this->subject->solution_type === $type && (int) $this->subject->solution_id === $m->id;
            $iAmAuthor = $this->isQuestion() && $this->viewerId && $this->subject->user_id === $this->viewerId;

            return new FeedEntry(
                message: $m,
                type: $type,
                anchor: ($type === 'comment' ? 'c-' : 'r-') . $m->id,
                rootId: $r['root'],
                body: $body,
                photos: $photos,
                firstInSeries: ! $prev || $prev->user_id !== $m->user_id || ! $this->sameDay($prev, $m),
                lastInSeries: ! $next || $next->user_id !== $m->user_id || ! $this->sameDay($next, $m),
                startsDay: ! $prev || ! $this->sameDay($prev, $m),
                colorSlot: $m->user_id % self::COLORS,
                byQuestionAuthor: $this->isQuestion() && $m->user_id === $this->subject->user_id,
                isSolution: $isSolution,
                canMarkSolution: $iAmAuthor && ! $isSolution && $m->user_id !== $this->viewerId,
                solutions: (int) ($solutions[$m->user_id] ?? 0),
                quote: $r['quoted'] ? $this->quote($r['quoted']) : null,
                preview: PlainText::from($m->text, 80),
            );
        });
    }

    private function quote(Comment|Reply $message): FeedQuote
    {
        return new FeedQuote(
            anchor: ($message instanceof Reply ? 'r-' : 'c-') . $message->id,
            authorName: $message->user->name,
            colorSlot: $message->user_id % self::COLORS,
            text: PlainText::from($message->text),
        );
    }

    private function sameDay(Comment|Reply $a, Comment|Reply $b): bool
    {
        return $a->created_at->inDisplayTimezone()->toDateString() === $b->created_at->inDisplayTimezone()->toDateString();
    }
}
