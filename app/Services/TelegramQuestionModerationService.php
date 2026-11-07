<?php

namespace App\Services;

use App\Actions\SitePublication\AppendToPublishedQuestion;
use App\Actions\SitePublication\PublicationState;
use App\Actions\SitePublication\PublishQuestion;
use App\Actions\SitePublication\RecordsModerationActions;
use App\Actions\SitePublication\RejectPublication;
use App\Enums\TelegramQuestionConfirmationStatus;
use App\Enums\TelegramQuestionResolutionStatus;
use Illuminate\Support\Facades\DB;

class TelegramQuestionModerationService
{
    use RecordsModerationActions;

    public function __construct(
        private readonly TelegramBotApi $telegram,
    ) {}

    /** @param array<string, mixed> $payload */
    public function preview(int $updateId, array $payload, bool $notifySameChat = true): bool
    {
        $message = $payload['message'] ?? null;
        if (! is_array($message)) {
            return false;
        }

        $chatId = (int) ($message['chat']['id'] ?? 0);
        $messageId = (int) ($message['message_id'] ?? 0);
        $text = trim((string) ($message['text'] ?? $message['caption'] ?? ''));
        if ($chatId === 0 || $messageId === 0 || $text === '') {
            return false;
        }

        $moderationChatId = (int) (config('services.telegram.moderation_chat_id') ?: $chatId);
        $record = DB::table('telegram_question_messages')
            ->where('telegram_update_id', $updateId)
            ->first();
        if (! $record || (PublicationState::forMessage($record)->status ?? null) !== 'pending_moderation') {
            return false;
        }

        $result = $moderationChatId === $chatId && ! $notifySameChat
            ? []
            : $this->telegram->call('sendMessage', [
                'chat_id' => $moderationChatId,
                'text' => $this->previewText($record->id, $text, $message, $moderationChatId !== $chatId),
                'disable_web_page_preview' => true,
            ] + $this->thread());

        DB::table('telegram_question_messages')->where('id', $record->id)->update([
            'moderation_chat_id' => $moderationChatId,
            'moderation_message_id' => (int) ($result['message_id'] ?? 0) ?: null,
            'updated_at' => now(),
        ]);

        if ($moderationChatId !== $chatId) {
            $this->sendPreviewPhotos((int) $record->id, $payload, $moderationChatId);
        }

        return true;
    }

    public function approve(int $queueId, int $applianceId, ?int $moderatorId): string
    {
        return PublishQuestion::run($queueId, $applianceId, $moderatorId);
    }

    public function reject(int $queueId, string $reason, ?int $moderatorId): string
    {
        return RejectPublication::run($queueId, $reason, $moderatorId);
    }

    public function retry(int $queueId, ?int $moderatorId): string
    {
        $record = DB::table('telegram_question_messages')->where('id', $queueId)->first();
        if (! $record) {
            return 'Сообщение для модерации не найдено.';
        }

        $payload = json_decode((string) $record->payload, true, 32, JSON_THROW_ON_ERROR);
        $this->preview((int) $record->telegram_update_id, $payload);

        return 'Preview отправлен повторно.';
    }

    /** @param array<string, mixed> $payload */
    public function attachCommunity(int $id, array $payload): void
    {
        DB::transaction(function () use ($id, $payload): void {
            $record = DB::table('telegram_question_messages')->where('id', $id)->lockForUpdate()->first();
            if (! $record || $record->archived_at !== null || $record->resolution_status !== TelegramQuestionResolutionStatus::Open->value
                || ($record->registered_at === null && $record->community_confirmation_status !== TelegramQuestionConfirmationStatus::Pending->value)) {
                return;
            }
            $stored = json_decode((string) $record->payload, true) ?: [];
            $messageId = (int) data_get($payload, 'message.message_id');
            $ids = array_map('intval', (array) ($stored['attached_message_ids'] ?? []));
            if ($messageId <= 0 || in_array($messageId, $ids, true)) {
                return;
            }
            $text = trim((string) data_get($payload, 'message.text', data_get($payload, 'message.caption', '')));
            $stored['attached_message_ids'][] = $messageId;
            $stored['attached_messages'][] = $payload['message'];
            DB::table('telegram_question_messages')->where('id', $id)->update([
                'text' => trim((string) $record->text.($text !== '' ? "\n\n".$text : '')),
                'payload' => json_encode($stored, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                'updated_at' => now(),
            ]);
        });
    }

    /** @param array<string, mixed> $payload */
    public function attach(int $queueId, array $payload, ?int $moderatorId): string
    {
        $outcome = DB::transaction(function () use ($queueId, $payload, $moderatorId): array {
            $record = DB::table('telegram_question_messages')->where('id', $queueId)->lockForUpdate()->first();
            if (! $record) {
                return ['message' => 'Запись очереди не найдена.'];
            }
            $publication = PublicationState::lockForMessage($record);
            $record->status = $publication->status ?? ($record->registered_at !== null ? 'registered' : ($record->community_confirmation_status === TelegramQuestionConfirmationStatus::Pending->value ? 'awaiting_confirmation' : 'unavailable'));
            if (! in_array($record->status, ['awaiting_confirmation', 'registered', 'pending_moderation', 'published'], true)) {
                return ['message' => 'К вопросу с текущим статусом нельзя добавлять сообщения.'];
            }
            if ($record->status === 'published') {
                return ['published' => true];
            }

            $messageId = (int) data_get($payload, 'message.message_id');
            $text = trim((string) data_get($payload, 'message.text', data_get($payload, 'message.caption', '')));
            $storedPayload = json_decode((string) $record->payload, true) ?: [];
            $attachedMessageIds = array_map('intval', (array) ($storedPayload['attached_message_ids'] ?? []));
            if ($messageId > 0 && in_array($messageId, $attachedMessageIds, true)) {
                return ['message' => 'Это сообщение уже прикреплено к вопросу.'];
            }

            $combinedText = trim((string) $record->text);
            if ($text !== '') {
                $combinedText = $combinedText === '' ? $text : $combinedText."\n\n".$text;
            }
            $storedPayload['attached_message_ids'][] = $messageId;
            $storedPayload['attached_messages'][] = $payload['message'];
            DB::table('telegram_question_messages')->where('id', $record->id)->update([
                'text' => $combinedText,
                'payload' => json_encode($storedPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                'updated_at' => now(),
            ]);
            $this->audit($moderatorId, 'attach', ['queue_id' => $queueId, 'message_id' => $messageId, 'deferred' => true]);

            $moderationChatId = (int) (config('services.telegram.moderation_chat_id') ?: $record->telegram_chat_id);
            return [
                'message' => $record->status === 'pending_moderation'
                    ? 'Сообщение добавлено к вопросу. Оно будет опубликовано вместе с ним.'
                    : 'Сообщение добавлено к вопросу в Telegram.',
                'preview_chat' => $record->status === 'pending_moderation' && $moderationChatId !== (int) $record->telegram_chat_id ? $moderationChatId : null,
            ];
        });

        if ($outcome['published'] ?? false) {
            return AppendToPublishedQuestion::run($queueId, $payload, $moderatorId);
        }
        if ($outcome['preview_chat'] ?? null) {
            $this->sendPreviewPhotos($queueId, $payload, $outcome['preview_chat']);
        }

        return $outcome['message'];
    }

    private function previewText(int $recordId, string $text, array $message, bool $private): string
    {
        if (! $private) {
            return 'Сообщение получено и передано на модерацию.';
        }

        $author = (string) data_get($message, 'from.username', data_get($message, 'from.first_name', 'unknown'));

        return "Новый вопрос #{$recordId}\nАвтор Telegram: {$author}\n\n{$text}\n\nМодерация выполняется в MoonShine → Вопросы → Telegram-модерация.";
    }

    /** @param array<string, mixed> $payload */
    private function sendPreviewPhotos(int $recordId, array $payload, int $chatId): void
    {
        $groups = [];
        $main = data_get($payload, 'message.photo');
        if (is_array($main) && $main !== []) {
            $groups[] = $main;
        }
        foreach ((array) data_get($payload, 'message.album_photos', []) as $photos) {
            if (is_array($photos) && $photos !== []) {
                $groups[] = $photos;
            }
        }
        foreach ((array) data_get($payload, 'attached_messages', []) as $message) {
            $photos = data_get($message, 'photo');
            if (is_array($photos) && $photos !== []) {
                $groups[] = $photos;
            }
        }

        $sent = [];
        foreach ($groups as $photos) {
            $photo = end($photos);
            $fileId = (string) data_get($photo, 'file_id');
            if ($fileId === '' || isset($sent[$fileId])) {
                continue;
            }
            $sent[$fileId] = true;
            try {
                $this->telegram->call('sendPhoto', [
                    'chat_id' => $chatId,
                    'photo' => $fileId,
                    'caption' => "Фото к вопросу #{$recordId}",
                ] + $this->thread());
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    /** @return array<string, int> */
    private function thread(): array
    {
        $thread = (int) config('services.telegram.moderation_thread_id');

        return $thread > 1 ? ['message_thread_id' => $thread] : [];
    }
}
