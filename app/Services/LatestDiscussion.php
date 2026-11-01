<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Firmware;
use App\Models\Post;
use App\Models\Question;
use App\Models\Reply;
use App\Support\PlainText;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class LatestDiscussion
{
    public const CACHE_KEY = 'latest_discussions_widget';

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /** @return Collection<int, array{message: Comment|Reply, subject: mixed, url: string, excerpt: string, kind: string, firstInSeries: bool, lastInSeries: bool}> */
    public function get(int $limit = 5): Collection
    {
        $comments = Comment::query()->with(['user.avatar', 'commentable'])->latest('created_at')->limit($limit)->get();
        $replies = Reply::query()->with(['user.avatar', 'comment.commentable'])->latest('created_at')->limit($limit)->get();

        $items = $comments
            ->map(fn (Comment $message) => $this->item($message, $message->commentable, 'c-'.$message->id))
            ->merge($replies->map(fn (Reply $message) => $this->item($message, $message->comment->commentable, 'r-'.$message->id)))
            ->filter()
            ->sortByDesc(fn (array $item) => $item['message']->created_at)
            ->take($limit)
            ->values();

        return $items->map(function (array $item, int $index) use ($items): array {
            $previous = $items[$index - 1]['message'] ?? null;
            $next = $items[$index + 1]['message'] ?? null;
            $sameSeries = fn ($message) => $message
                && $message->user_id === $item['message']->user_id
                && $message->created_at->inDisplayTimezone()->toDateString() === $item['message']->created_at->inDisplayTimezone()->toDateString();

            return $item + [
                'kind' => $item['message'] instanceof Reply ? 'Ответ' : 'Комментарий',
                'firstInSeries' => ! $sameSeries($previous),
                'lastInSeries' => ! $sameSeries($next),
            ];
        });
    }

    /** @return array{message: Comment|Reply, subject: mixed, url: string, excerpt: string}|null */
    private function item(Comment|Reply $message, mixed $subject, string $anchor): ?array
    {
        $url = match (true) {
            $subject instanceof Post => route('articles.show', ['article' => $subject->slug]).'#'.$anchor,
            $subject instanceof Question => route('questions.show', ['question' => $subject->slug]).'#'.$anchor,
            $subject instanceof Firmware => route('firmwares.show', ['firmware' => $subject->id]).'#'.$anchor,
            default => null,
        };

        return $url ? [
            'message' => $message,
            'subject' => $subject,
            'url' => $url,
            'excerpt' => PlainText::from($message->text, 110) ?: 'Фото',
        ] : null;
    }
}
