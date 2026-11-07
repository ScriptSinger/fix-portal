<?php

namespace App\Actions\TelegramQuestions;

use App\Services\TelegramQuestionModerationService;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class AttachReply
{
    use AsAction;

    public function __construct(private TelegramQuestionModerationService $questionModeration) {}

    /** @param list<string> $arguments  @param array<string, mixed> $message */
    public function handle(array $arguments, array $message, int $moderatorTelegramId): string
    {
        $queueId = (int) ($arguments[0] ?? 0);
        $reply = $message['reply_to_message'] ?? null;
        if (! is_array($reply)) {
            return 'Ответьте командой /attach [queue_id] на сообщение с текстом или фото.';
        }

        if ($queueId < 1) {
            $authorId = (int) data_get($reply, 'from.id');
            $queueId = (int) DB::table('telegram_question_messages')
                ->where('telegram_chat_id', (int) ($message['chat']['id'] ?? 0))
                ->where('telegram_user_id', $authorId)
                ->whereNotNull('registered_at')
                ->whereNull('archived_at')
                ->where('resolution_status', 'open')
                ->where('telegram_thread_id', max(0, (int) ($message['message_thread_id'] ?? 0) > 1 ? (int) $message['message_thread_id'] : 0))
                ->latest('id')
                ->value('id');

            if ($queueId < 1) {
                return 'У автора нет открытого вопроса в этой теме. Укажите ID явно.';
            }
        }

        $payload = ['message' => $reply];
        $mediaGroupId = (string) ($reply['media_group_id'] ?? '');
        if ($mediaGroupId !== '') {
            $albumPhotos = app(\App\Telegram\TelegramAlbum::class)->collect($reply, $mediaGroupId);
            if ($albumPhotos !== []) {
                $payload['message']['album_photos'] = $albumPhotos;
            }
        }

        return $this->questionModeration->attach($queueId, $payload, $moderatorTelegramId);
    }

}
