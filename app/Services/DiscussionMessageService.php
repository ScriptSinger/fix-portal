<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Firmware;
use App\Models\Post;
use App\Models\Question;
use App\Models\Reply;
use App\Support\MessageBody;

class DiscussionMessageService
{
    public function makeText(?string $text, array $images, int $userId, array $keep = []): string
    {
        $body = MessageBody::make($text, $images, $userId, $keep);
        abort_if($body === '', 422, 'Пустое сообщение');

        return $body;
    }

    public function update(Comment|Reply $message, array $data, int $userId): void
    {
        $oldImageIds = app(ImageCleanupService::class)->idsFromHtml($message->text);

        $message->update([
            'text' => $this->makeText(
                $data['text'] ?? null,
                $data['images'] ?? [],
                $userId,
                $this->existingImages($message->text),
            ),
        ]);

        app(ImageCleanupService::class)->cleanup($oldImageIds);
    }

    public function plain(Comment|Reply $message): string
    {
        return MessageBody::toPlain($message->text);
    }

    public function rendered(Comment|Reply $message): string
    {
        return view('public.partials.discussion-content', ['message' => $message])->render();
    }

    public function url(Comment|Reply $message): string
    {
        $subject = $message instanceof Reply
            ? $message->comment->commentable
            : $message->commentable;

        return match (true) {
            $subject instanceof Post => route('articles.show', ['article' => $subject->slug]),
            $subject instanceof Question => route('questions.show', ['question' => $subject->slug]),
            $subject instanceof Firmware => route('firmwares.show', ['firmware' => $subject->id]),
            default => url('/'),
        };
    }

    /** @return list<string> */
    private function existingImages(?string $body): array
    {
        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', (string) $body, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }
}
