<?php

namespace App\Actions\TelegramQuestions;

use App\Actions\SitePublication\SubmitForModeration;
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
            return 'Ответьте командой /moderate на сообщение, которое нужно передать на модерацию сайта.';
        }

        $authorId = (int) data_get($reply, 'from.id');
        $maxPending = max(1, (int) config('services.telegram.max_pending_per_user', 5));
        if ($authorId > 0 && DB::table('telegram_question_publications')
            ->join('telegram_question_messages', 'telegram_question_messages.id', '=', 'telegram_question_publications.telegram_question_message_id')
            ->where('telegram_user_id', $authorId)
            ->where('telegram_question_publications.status', 'pending_moderation')
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
        $record = DB::table('telegram_question_messages')
            ->where('telegram_chat_id', (int) data_get($reply, 'chat.id', data_get($message, 'chat.id')))
            ->where('telegram_message_id', (int) data_get($reply, 'message_id'))
            ->first();
        if (! $record) {
            if (! $this->questionIntake->store($updateId, $payload, true)) {
                return 'Не удалось зарегистрировать вопрос: сообщение уже обработано или не содержит текста.';
            }
            $record = DB::table('telegram_question_messages')
                ->where('telegram_chat_id', (int) data_get($reply, 'chat.id', data_get($message, 'chat.id')))
                ->where('telegram_message_id', (int) data_get($reply, 'message_id'))
                ->first();
        }

        if (! $record) {
            return 'Вопрос не найден.';
        }
        $result = DB::transaction(function () use ($record, $message, $payload): string {
            $current = DB::table('telegram_question_messages')->where('id', $record->id)->lockForUpdate()->first();
            if (! $current) {
                return 'Вопрос не найден.';
            }
            $result = SubmitForModeration::run((int) $current->id, (int) data_get($message, 'from.id'));
            if ($result !== 'Сообщение передано на модерацию.') {
                return $result;
            }
            $photos = data_get($payload, 'message.album_photos', []);
            if ($photos !== []) {
                $stored = json_decode((string) $current->payload, true) ?: [];
                $groups = array_merge((array) data_get($stored, 'message.album_photos', []), $photos);
                $unique = [];
                foreach ($groups as $group) {
                    if (! is_array($group) || $group === []) {
                        continue;
                    }
                    $largest = end($group);
                    $key = (string) data_get($largest, 'file_unique_id', data_get($largest, 'file_id', ''));
                    if ($key !== '') {
                        $unique[$key] = $group;
                    }
                }
                data_set($stored, 'message.album_photos', array_values($unique));
                DB::table('telegram_question_messages')->where('id', $current->id)->update([
                    'payload' => json_encode($stored, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                    'updated_at' => now(),
                ]);
            }

            return $result;
        });
        if ($result !== 'Сообщение передано на модерацию.') {
            return $result;
        }

        $storedPayload = json_decode((string) DB::table('telegram_question_messages')->where('id', $record->id)->value('payload'), true) ?: $payload;
        $this->questionModeration->preview((int) $record->telegram_update_id, $storedPayload, false);

        return $result;
    }

}
