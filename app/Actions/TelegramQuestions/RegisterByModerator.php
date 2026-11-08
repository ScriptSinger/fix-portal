<?php

namespace App\Actions\TelegramQuestions;

use App\Enums\TelegramQuestionConfirmationStatus;
use App\Services\TelegramQuestionIntake;
use App\Telegram\TelegramAlbum;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class RegisterByModerator
{
    use AsAction;

    public function handle(int $updateId, array $message): string
    {
        $moderator = (int) data_get($message, 'from.id');
        if (! in_array($moderator, config('services.telegram.moderator_ids', []), true)) {
            return 'Нет доступа к командам модератора.';
        }
        $reply = data_get($message, 'reply_to_message');
        if (! is_array($reply)) {
            return 'Ответьте командой /question на сообщение с вопросом.';
        }
        $chatId = (int) data_get($message, 'chat.id');
        $threadId = (int) data_get($message, 'message_thread_id', 0);
        $sourceThread = (int) config('services.telegram.source_thread_id');
        if ($chatId === 0 || (string) $chatId !== (string) config('services.telegram.source_chat_id')
            || ($sourceThread > 1 && $threadId !== $sourceThread)
            || (int) data_get($reply, 'chat.id', $chatId) !== $chatId) {
            return 'Команда доступна только в настроенной группе и теме.';
        }
        if ((int) data_get($reply, 'from.id') <= 0 || data_get($reply, 'from.is_bot') || isset($reply['sender_chat'])) {
            return 'Выберите сообщение участника группы.';
        }
        $reply['chat'] ??= $message['chat'];
        if ($threadId > 1) {
            $reply['message_thread_id'] ??= $threadId;
        }
        $mediaGroupId = (string) ($reply['media_group_id'] ?? '');
        if ($mediaGroupId !== '') {
            $reply['album_photos'] = app(TelegramAlbum::class)->collect($reply, $mediaGroupId);
            if (trim((string) ($reply['text'] ?? $reply['caption'] ?? '')) === '') {
                $updates = DB::table('telegram_webhook_updates')
                    ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.message.media_group_id')) = ?", [$mediaGroupId])
                    ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.message.chat.id')) = ?", [(string) $chatId])
                    ->get(['payload']);
                foreach ($updates as $update) {
                    $caption = trim((string) data_get(json_decode((string) $update->payload, true), 'message.caption', ''));
                    if ($caption !== '') {
                        $reply['caption'] = $caption;
                        break;
                    }
                }
            }
        }
        $messageId = (int) ($reply['message_id'] ?? 0);
        if ($messageId <= 0) {
            return 'Сообщение с вопросом не найдено.';
        }

        $record = DB::table('telegram_question_messages')->where('telegram_chat_id', $chatId)->where('telegram_message_id', $messageId)->first();
        if (! $record) {
            app(TelegramQuestionIntake::class)->storeForGroup($updateId, ['message' => $reply]);
        }

        return DB::transaction(function () use ($chatId, $messageId, $reply): string {
            $record = DB::table('telegram_question_messages')->where('telegram_chat_id', $chatId)->where('telegram_message_id', $messageId)->lockForUpdate()->first();
            if (! $record) {
                return 'Не удалось зарегистрировать вопрос: сообщение не содержит текста или фото.';
            }
            if ($record->registered_at !== null) {
                return 'Этот вопрос уже зарегистрирован.';
            }
            if ($record->archived_at !== null || $record->community_confirmation_status === TelegramQuestionConfirmationStatus::Declined->value) {
                return 'Этот вопрос нельзя зарегистрировать: автор отказался или запись архивирована.';
            }
            $stored = json_decode((string) $record->payload, true) ?: ['message' => $reply];
            if (! empty($reply['album_photos'])) {
                $groups = array_merge((array) data_get($stored, 'message.album_photos', []), $reply['album_photos']);
                $unique = [];
                foreach ($groups as $group) {
                    if (! is_array($group) || $group === []) {
                        continue;
                    }
                    $photo = end($group);
                    $key = (string) data_get($photo, 'file_unique_id', data_get($photo, 'file_id', ''));
                    if ($key !== '') {
                        $unique[$key] = $group;
                    }
                }
                data_set($stored, 'message.album_photos', array_values($unique));
            }
            DB::table('telegram_question_messages')->where('id', $record->id)->update([
                'registered_at' => now(), 'community_confirmation_status' => TelegramQuestionConfirmationStatus::Confirmed->value,
                'text' => trim((string) $record->text) !== '' ? $record->text : trim((string) ($reply['text'] ?? $reply['caption'] ?? '')),
                'payload' => json_encode($stored, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                'updated_at' => now(),
            ]);

            return 'registered';
        });
    }
}
