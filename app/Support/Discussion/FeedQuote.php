<?php

namespace App\Support\Discussion;

/** Цитата в начале ответа: на какое сообщение отвечали. */
final class FeedQuote
{
    public function __construct(
        public readonly string $anchor,
        public readonly string $authorName,
        public readonly int $colorSlot,
        public readonly string $text,
    ) {
    }
}
