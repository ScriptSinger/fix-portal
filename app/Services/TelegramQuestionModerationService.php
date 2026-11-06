<?php

namespace App\Services;

use App\Models\Appliance;
use App\Models\Image;
use App\Models\Question;
use App\Services\Announcements\QuestionAnnouncer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TelegramQuestionModerationService
{
    public function __construct(
        private readonly TelegramBotApi $telegram,
        private readonly QuestionAnnouncer $announcer,
    )
    {
    }

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
        if (! $record || $record->status !== 'pending_moderation') {
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

    public function approve(int $messageId, int $applianceId, ?int $moderatorId): string
    {
        return DB::transaction(function () use ($messageId, $applianceId, $moderatorId): string {
            $record = DB::table('telegram_question_messages')->where('telegram_message_id', $messageId)->lockForUpdate()->first();
            if (! $record) {
                return 'Сообщение для модерации не найдено.';
            }
            if ($record->status === 'published') {
                return "Вопрос уже опубликован: #{$record->question_id}.";
            }
            if ($record->status !== 'pending_moderation') {
                return "Нельзя опубликовать: текущий статус {$record->status}.";
            }
            if (! Appliance::query()->whereKey($applianceId)->exists()) {
                return 'Тип прибора с таким ID не найден.';
            }
            $author = \App\Models\User::query()->find((int) config('services.telegram.author_user_id'));
            if (! $author) {
                return 'Не настроен TELEGRAM_AUTHOR_USER_ID.';
            }

            $text = trim((string) $record->text);
            $storedPayload = json_decode((string) $record->payload, true) ?: [];
            $attachedMessages = is_array($storedPayload['attached_messages'] ?? null)
                ? $storedPayload['attached_messages']
                : [];
            $descriptionParts = array_values(array_filter([
                $text,
                ...array_map(static fn (array $message): string => trim((string) ($message['text'] ?? $message['caption'] ?? '')), $attachedMessages),
            ], static fn (string $value): bool => $value !== ''));
            $lines = preg_split('/\R/u', $text, 2) ?: [$text];
            $title = Str::limit(trim($lines[0]) ?: 'Вопрос из Telegram', 150, '');
            if (mb_strlen($title) < 12) {
                $title = Str::limit($text ?: 'Вопрос из Telegram', 150, '');
            }
            $images = $this->importPhotos($record);
            foreach ($attachedMessages as $attachedMessage) {
                $images = [...$images, ...$this->importPhotosPayload((int) $record->id, ['message' => $attachedMessage])];
            }
            $description = e(implode("\n\n", $descriptionParts));
            foreach ($images as $image) {
                $description .= '<img src="'.e($image['url']).'" class="img-fluid" data-id="'.$image['id'].'">';
            }
            $question = Question::create([
                'title' => $title,
                'description' => $description,
                'appliance_id' => $applianceId,
                'user_id' => $author->id,
            ]);

            DB::table('telegram_question_messages')->where('id', $record->id)->update([
                'status' => 'published',
                'question_id' => $question->id,
                'updated_at' => now(),
            ]);
            $this->audit($moderatorId, $question->id, 'approve', ['message_id' => $messageId]);

            try {
                $this->telegram->call('sendMessage', $this->publishedMessagePayload($record, $question));
            } catch (\Throwable $e) {
                DB::table('telegram_question_messages')->where('id', $record->id)->update([
                    'error_message' => 'Вопрос опубликован, но ссылка не отправлена в Telegram: '.Str::limit($e->getMessage(), 900),
                    'updated_at' => now(),
                ]);
            }

            return "Вопрос опубликован: ".route('questions.show', ['question' => $question->slug]);
        });
    }

    public function reject(int $messageId, string $reason, ?int $moderatorId): string
    {
        $record = DB::table('telegram_question_messages')->where('telegram_message_id', $messageId)->first();
        if (! $record) {
            return 'Сообщение для модерации не найдено.';
        }
        if ($record->status !== 'pending_moderation') {
            return "Нельзя отклонить: текущий статус {$record->status}.";
        }

        DB::table('telegram_question_messages')->where('id', $record->id)->update([
            'status' => 'rejected',
            'error_message' => $reason !== '' ? Str::limit($reason, 1000) : null,
            'updated_at' => now(),
        ]);
        $this->audit($moderatorId, null, 'reject', ['message_id' => $messageId, 'reason' => $reason]);

        return 'Вопрос отклонён.';
    }

    public function retry(int $messageId, ?int $moderatorId): string
    {
        $record = DB::table('telegram_question_messages')->where('telegram_message_id', $messageId)->first();
        if (! $record) {
            return 'Сообщение для модерации не найдено.';
        }

        $payload = json_decode((string) $record->payload, true, 32, JSON_THROW_ON_ERROR);
        $this->preview((int) $record->telegram_update_id, $payload);

        return 'Preview отправлен повторно.';
    }

    /** @param array<string, mixed> $payload */
    public function attach(int $queueId, array $payload, ?int $moderatorId): string
    {
        $record = DB::table('telegram_question_messages')->where('id', $queueId)->first();
        if (! $record) {
            return 'Запись очереди не найдена.';
        }
        if (! in_array($record->status, ['pending_moderation', 'published'], true)) {
            return 'К вопросу с текущим статусом нельзя добавлять сообщения.';
        }

        $messageId = (int) data_get($payload, 'message.message_id');
        $text = trim((string) data_get($payload, 'message.text', data_get($payload, 'message.caption', '')));
        $storedPayload = json_decode((string) $record->payload, true) ?: [];
        $attachedMessageIds = array_map('intval', (array) ($storedPayload['attached_message_ids'] ?? []));
        if ($messageId > 0 && in_array($messageId, $attachedMessageIds, true)) {
            return 'Это сообщение уже прикреплено к вопросу.';
        }

        if ($record->status === 'pending_moderation') {
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
            $this->audit($moderatorId, null, 'attach', ['queue_id' => $queueId, 'message_id' => $messageId, 'deferred' => true]);

            $moderationChatId = (int) (config('services.telegram.moderation_chat_id') ?: $record->telegram_chat_id);
            if ($moderationChatId !== (int) $record->telegram_chat_id) {
                $this->sendPreviewPhotos((int) $record->id, $payload, $moderationChatId);
            }

            return 'Сообщение добавлено к вопросу. Оно будет опубликовано вместе с ним.';
        }

        try {
            $images = $this->importPhotosPayload((int) $record->id, $payload);
        } catch (\Throwable $e) {
            report($e);

            return 'Не удалось скачать вложение из Telegram. Проверьте proxy и повторите команду.';
        }
        if ($images === [] && $text === '') {
            return 'В ответном сообщении нет текста или фото.';
        }

        $question = Question::query()->find($record->question_id);
        if (! $question) {
            return 'Опубликованный вопрос не найден.';
        }

        if ($text !== '') {
            $question->description .= "\n\n".e($text);
        }
        foreach ($images as $image) {
            $question->description .= '<img src="'.e($image['url']).'" class="img-fluid" data-id="'.$image['id'].'">';
        }
        $question->save();
        if ($messageId > 0) {
            $attachedMessageIds[] = $messageId;
            $storedPayload['attached_message_ids'] = array_values(array_unique($attachedMessageIds));
            DB::table('telegram_question_messages')->where('id', $record->id)->update([
                'payload' => json_encode($storedPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                'updated_at' => now(),
            ]);
        }
        try {
            $this->announcer->refresh($question);
        } catch (\Throwable $e) {
            report($e);
        }
        $this->audit($moderatorId, $question->id, 'attach', [
            'queue_id' => $queueId,
            'message_id' => $messageId,
            'images' => count($images),
            'has_text' => $text !== '',
        ]);

        return 'Сообщение и вложения добавлены к опубликованному вопросу.';
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

    /** @return list<array{id: int, url: string}> */
    private function importPhotos(object $record): array
    {
        $payload = json_decode((string) $record->payload, true) ?: [];
        return $this->importPhotosPayload((int) $record->id, $payload);
    }

    /** @param array<string, mixed> $payload */
    private function importPhotosPayload(int $recordId, array $payload): array
    {
        $albums = data_get($payload, 'message.album_photos', []);
        if (! is_array($albums) || $albums === []) {
            $albums = [data_get($payload, 'message.photo', [])];
        }

        $images = [];
        foreach ($albums as $photos) {
            if (! is_array($photos) || $photos === []) {
                continue;
            }

            $photo = end($photos);
            $fileId = (string) data_get($photo, 'file_id');
            if ($fileId === '') {
                continue;
            }

            $file = $this->telegram->call('getFile', ['file_id' => $fileId]);
            $filePath = (string) ($file['file_path'] ?? '');
            if ($filePath === '') {
                throw new \RuntimeException('Telegram photo path is missing.');
            }

            $contents = $this->telegram->downloadFile($filePath);
            if ($contents === '' || strlen($contents) > 10 * 1024 * 1024) {
                throw new \RuntimeException('Telegram photo is empty or too large.');
            }

            $disk = Storage::disk('public');
            $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($contents) ?: '';
            if (! str_starts_with($mime, 'image/')) {
                throw new \RuntimeException('Telegram file is not an image.');
            }

            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) ?: 'jpg';
            $path = 'images/telegram/'.$recordId.'/'.hash('sha256', $contents).'.'.$extension;
            $disk->put($path, $contents);
            $url = Storage::url($path);

            $image = new Image;
            $image->url = $url;
            $image->mime = $mime;
            $image->size = $disk->size($path);
            $image->user_id = (int) config('services.telegram.author_user_id');
            $image->save();

            $images[] = ['id' => (int) $image->id, 'url' => $url];
        }

        return $images;
    }

    /** @return array<string, int|string> */
    private function publishedMessagePayload(object $record, Question $question): array
    {
        $payload = json_decode((string) $record->payload, true) ?: [];
        $threadId = (int) data_get($payload, 'message.message_thread_id');

        return array_filter([
            'chat_id' => (int) $record->telegram_chat_id,
            'text' => "Вопрос опубликован на Ufamasters:\n".route('questions.show', ['question' => $question->slug]),
            'reply_to_message_id' => (int) $record->telegram_message_id,
            'message_thread_id' => $threadId > 1 ? $threadId : null,
            'disable_web_page_preview' => false,
        ], static fn (mixed $value): bool => $value !== null);
    }

    /** @return array<string, int> */
    private function thread(): array
    {
        $thread = (int) config('services.telegram.moderation_thread_id');

        return $thread > 1 ? ['message_thread_id' => $thread] : [];
    }

    /** @param array<string, mixed> $parameters */
    private function audit(?int $moderatorId, ?int $questionId, string $action, array $parameters): void
    {
        $parameters['source'] = $moderatorId === null ? 'moonshine' : 'telegram';

        DB::table('telegram_moderation_actions')->insert([
            'moderator_telegram_id' => $moderatorId ?? 0,
            'user_id' => null,
            'action' => $action,
            'parameters' => json_encode($parameters, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
