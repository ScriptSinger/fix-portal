<?php

namespace App\Actions\TelegramQuestions;

use App\Services\TelegramQuestionIntake;
use App\Services\TelegramQuestionModerationService;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class MarkQuestion
{
    use AsAction;

    public function __construct(private TelegramQuestionIntake $questionIntake, private TelegramQuestionModerationService $questionModeration) {}

    /** @param array<string, mixed> $message */
    public function handle(int $updateId, array $message): string
    {
        $reply = $message['reply_to_message'] ?? null;
        if (! is_array($reply)) {
            return 'Ответьте командой /question на сообщение, которое нужно передать на модерацию.';
        }

        $authorId = (int) data_get($reply, 'from.id');
        $maxPending = max(1, (int) config('services.telegram.max_pending_per_user', 5));
        if ($authorId > 0 && DB::table('telegram_question_messages')
            ->where('telegram_user_id', $authorId)
            ->where('status', 'pending_moderation')
            ->count() >= $maxPending) {
            return "У автора уже есть {$maxPending} вопрос(ов) на модерации.";
        }

        $payload = ['message' => $reply];
        $mediaGroupId = (string) ($reply['media_group_id'] ?? '');
        if ($mediaGroupId !== '') {
            $albumPhotos = app(\App\Telegram\TelegramAlbum::class)->collect($reply, $mediaGroupId);
            if ($albumPhotos !== []) {
                $payload['message']['album_photos'] = $albumPhotos;
            }
        }
        if (! $this->questionIntake->store($updateId, $payload, true)) {
            return 'Это сообщение уже передано на модерацию или не содержит текста.';
        }

        $this->questionModeration->preview($updateId, $payload, false);

        return 'Сообщение передано на модерацию.';
    }

}
