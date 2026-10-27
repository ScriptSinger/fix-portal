<?php

namespace App\Support\Discussion;

use App\Models\Comment;
use App\Models\Reply;

/** Одно сообщение ленты вместе со всем, что нужно для показа: шаблон только рисует. */
final class FeedEntry
{
    /**
     * @param  list<array{src: string, full: string}>  $photos
     */
    public function __construct(
        public readonly Comment|Reply $message,
        public readonly string $type,
        public readonly string $anchor,
        public readonly int $rootId,
        public readonly string $body,
        public readonly array $photos,
        public readonly bool $firstInSeries,
        public readonly bool $lastInSeries,
        public readonly bool $startsDay,
        public readonly int $colorSlot,
        public readonly bool $byQuestionAuthor,
        public readonly bool $isSolution,
        public readonly bool $canMarkSolution,
        public readonly int $solutions,
        public readonly ?FeedQuote $quote,
        public readonly string $preview,
    ) {
    }

    public function isReply(): bool
    {
        return $this->type === 'reply';
    }
}
