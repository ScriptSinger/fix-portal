<?php

namespace App\Services;

use App\Enums\TelegramQuestionResolutionStatus;
use App\Enums\TelegramQuestionConfirmationStatus;
use App\Telegram\TelegramAlbum;
use Illuminate\Support\Facades\DB;

class TelegramQuestionIntake
{
    /** @param array<string, mixed> $payload */
    public function pendingReplyContinuation(array $payload): ?object
    {
        $message = $payload['message'] ?? null;
        if (! is_array($message)) {
            return null;
        }

        $chatId = $message['chat']['id'] ?? null;
        $userId = $message['from']['id'] ?? null;
        if ($chatId === null || $userId === null) {
            return null;
        }

        $replyMessageId = (int) data_get($message, 'reply_to_message.message_id');
        if ($replyMessageId < 1) {
            return null;
        }

        $records = DB::table('telegram_question_messages')
            ->where('telegram_chat_id', (int) $chatId)
            ->where('telegram_user_id', (int) $userId)
            ->whereNull('archived_at')
            ->where('resolution_status', TelegramQuestionResolutionStatus::Open->value)
            ->where(function ($query): void {
                $query->whereNotNull('registered_at')->orWhere('community_confirmation_status', TelegramQuestionConfirmationStatus::Pending->value);
            })
            ->whereNotNull('text')
            ->where('text', '<>', '')
            ->latest('id')
            ->get();

        foreach ($records as $record) {
            if ((int) $record->telegram_message_id === $replyMessageId) {
                return $record;
            }

            $payload = json_decode((string) $record->payload, true) ?: [];
            $attachedIds = array_map('intval', (array) ($payload['attached_message_ids'] ?? []));
            if (in_array($replyMessageId, $attachedIds, true)) {
                return $record;
            }
        }

        return null;
    }

    /** @param array<string, mixed> $payload */
    public function store(int $updateId, array $payload, bool $allowReply = false): bool
    {
        return $this->storeRecord($updateId, $payload, $allowReply, 'pending_moderation') !== null;
    }

    /** @param array<string, mixed> $payload */
    public function storeAwaitingConfirmation(int $updateId, array $payload): ?int
    {
        $message = $payload['message'] ?? [];
        $group = (string) ($message['media_group_id'] ?? '');
        if ($group !== '' && is_array($message)) {
            $payload['message']['album_photos'] = app(TelegramAlbum::class)->collect($message, $group);
        }
        return $this->storeRecord($updateId, $payload, false, 'awaiting_confirmation');
    }

    /** @param array<string, mixed> $message */
    public function addAlbumPhoto(array $message): bool
    {
        $group = (string) ($message['media_group_id'] ?? '');
        $chatId = (int) data_get($message, 'chat.id');
        $authorId = (int) data_get($message, 'from.id');
        if ($group === '' || empty($message['photo']) || $chatId === 0 || $authorId <= 0
            || (string) $chatId !== (string) config('services.telegram.source_chat_id')) {
            return false;
        }

        return DB::transaction(function () use ($message, $group, $chatId, $authorId): bool {
            $record = DB::table('telegram_question_messages')
                ->where('telegram_chat_id', $chatId)->where('telegram_user_id', $authorId)
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.message.media_group_id')) = ?", [$group])
                ->lockForUpdate()->first();
            if (! $record) {
                return false;
            }
            $stored = json_decode((string) $record->payload, true) ?: [];
            $photos = app(TelegramAlbum::class)->collect($message, $group);
            $groups = array_merge((array) data_get($stored, 'message.album_photos', []), $photos);
            $unique = [];
            foreach ($groups as $photos) {
                if (! is_array($photos) || $photos === []) {
                    continue;
                }
                $largest = end($photos);
                $key = (string) data_get($largest, 'file_unique_id', data_get($largest, 'file_id', ''));
                if ($key !== '') {
                    $unique[$key] = $photos;
                }
            }
            data_set($stored, 'message.album_photos', array_values($unique));
            $caption = trim((string) ($message['caption'] ?? ''));
            DB::table('telegram_question_messages')->where('id', $record->id)->update([
                'payload' => json_encode($stored, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                'text' => trim((string) $record->text) !== '' ? $record->text : $caption,
                'updated_at' => now(),
            ]);

            return true;
        });
    }

    /** @param array<string, mixed> $payload */
    public function storeForGroup(int $updateId, array $payload): ?int
    {
        return $this->storeRecord($updateId, $payload, true, 'registered');
    }

    /** @param array<string, mixed> $payload */
    private function storeRecord(int $updateId, array $payload, bool $allowReply, string $status): ?int
    {
        $message = $payload['message'] ?? null;
        if (! is_array($message)) {
            return null;
        }

        $chatId = $message['chat']['id'] ?? null;
        $messageId = $message['message_id'] ?? null;
        $sourceChatId = config('services.telegram.source_chat_id');
        if ($chatId === null || $messageId === null || $sourceChatId === null || (string) $chatId !== (string) $sourceChatId) {
            return null;
        }

        // Ответы и комментарии не создают новые вопросы. Для них будет отдельный сценарий.
        if (! $allowReply && isset($message['reply_to_message'])) {
            return null;
        }

        $sourceThreadId = (int) config('services.telegram.source_thread_id');
        if ($sourceThreadId > 1 && (int) ($message['message_thread_id'] ?? 0) !== $sourceThreadId) {
            return null;
        }

        $text = trim((string) ($message['text'] ?? $message['caption'] ?? ''));
        $hasPhoto = is_array($message['photo'] ?? null) && $message['photo'] !== [];
        if ($text === '' && (! $allowReply || ! $hasPhoto)) {
            return null;
        }

        // Автоматически считаем сообщением-вопросом только текст с вопросительным знаком.
        // Команда /moderate передаёт allowReply=true и может явно пометить любой текст/фото.
        if (! $allowReply && ! str_contains($text, '?') && ! str_contains($text, '？')) {
            return null;
        }

        $inserted = DB::table('telegram_question_messages')->insertOrIgnore([
            'telegram_chat_id' => (int) $chatId,
            'telegram_message_id' => (int) $messageId,
            'telegram_update_id' => $updateId,
            'telegram_user_id' => isset($message['from']['id']) ? (int) $message['from']['id'] : null,
            'telegram_thread_id' => (int) ($message['message_thread_id'] ?? 0) > 1 ? (int) $message['message_thread_id'] : 0,
            'text' => $text,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            'status' => $status,
            'community_confirmation_status' => $status === 'awaiting_confirmation' ? TelegramQuestionConfirmationStatus::Pending->value : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($inserted !== 1) {
            // Retry the same update after a failed confirmation prompt, without creating a second question.
            if ($status === 'awaiting_confirmation') {
                $existingId = DB::table('telegram_question_messages')
                    ->where('telegram_chat_id', (int) $chatId)
                    ->where('telegram_message_id', (int) $messageId)
                    ->where('telegram_update_id', $updateId)
                    ->where('status', $status)
                    ->where('community_confirmation_status', TelegramQuestionConfirmationStatus::Pending->value)
                    ->value('id');

                return $existingId === null ? null : (int) $existingId;
            }

            return null;
        }

        $id = (int) DB::table('telegram_question_messages')
            ->where('telegram_chat_id', (int) $chatId)
            ->where('telegram_message_id', (int) $messageId)
            ->value('id');

        return $id;
    }
}
