<?php

namespace App\Actions\SitePublication;

use App\Models\Appliance;
use App\Models\Question;
use App\Models\User;
use App\Services\TelegramPhotoImporter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class PublishQuestion
{
    use AsAction, RecordsModerationActions;

    public function __construct(private readonly TelegramPhotoImporter $photos) {}

    /** $queueId is the database ID, never a chat-scoped Telegram message ID. */
    public function handle(int $queueId, int $applianceId, ?int $moderatorId): string
    {
        $snapshot = DB::table('telegram_question_messages')->where('id', $queueId)->first();
        if (! $snapshot) {
            return 'Сообщение для модерации не найдено.';
        }
        if ($response = $this->cannotPublish(PublicationState::forMessage($snapshot))) {
            return $response;
        }
        if (! Appliance::query()->whereKey($applianceId)->exists()) {
            return 'Тип прибора с таким ID не найден.';
        }
        $author = User::query()->find((int) config('services.telegram.author_user_id'));
        if (! $author) {
            return 'Не настроен TELEGRAM_AUTHOR_USER_ID.';
        }

        // Telegram and storage I/O must not hold publication/message row locks.
        $files = [];
        $error = null;
        try {
            $files = $this->photos->prepare($queueId, json_decode((string) $snapshot->payload, true) ?: []);
        } catch (\Throwable $e) {
            report($e);
            $error = 'Не удалось импортировать фотографии: '.Str::limit($e->getMessage(), 700);
        }

        return DB::transaction(function () use ($queueId, $applianceId, $moderatorId, $snapshot, $author, $files, $error): string {
            $record = DB::table('telegram_question_messages')->where('id', $queueId)->lockForUpdate()->first();
            if (! $record) {
                return 'Сообщение для модерации не найдено.';
            }
            $publication = PublicationState::lockForMessage($record);
            if ($response = $this->cannotPublish($publication)) {
                return $response;
            }
            // An attachment may have arrived while photos were downloading. Do not
            // publish an old snapshot or let a late download failure undo a decision.
            if ($record->payload !== $snapshot->payload || $record->text !== $snapshot->text) {
                return 'Вопрос изменился во время импорта фотографий. Повторите публикацию.';
            }
            if ($error !== null) {
                PublicationState::update($publication, [
                    'status' => 'failed', 'appliance_id' => $applianceId, 'error_message' => $error,
                ]);
                $this->audit($moderatorId, 'approve_failed', ['message_id' => $record->telegram_message_id, 'reason' => $error]);

                return 'Публикация остановлена: фото не удалось импортировать. Исправьте ошибку и повторите публикацию.';
            }

            $text = trim((string) $record->text);
            $payload = json_decode((string) $record->payload, true) ?: [];
            $attached = is_array($payload['attached_messages'] ?? null) ? $payload['attached_messages'] : [];
            $parts = array_values(array_filter([
                $text,
                ...array_map(static fn (array $message): string => trim((string) ($message['text'] ?? $message['caption'] ?? '')), $attached),
            ], static fn (string $value): bool => $value !== ''));
            $lines = preg_split('/\R/u', $text, 2) ?: [$text];
            $title = Str::limit(trim($lines[0]) ?: 'Вопрос из Telegram', 150, '');
            if (mb_strlen($title) < 12) {
                $title = Str::limit($text ?: 'Вопрос из Telegram', 150, '');
            }
            $description = e(implode("\n\n", $parts));
            foreach ($this->photos->persist($files) as $image) {
                $description .= '<img src="'.e($image['url']).'" class="img-fluid" data-id="'.$image['id'].'">';
            }
            $question = Question::create([
                'title' => $title,
                'description' => $description,
                'appliance_id' => $applianceId,
                'user_id' => $author->id,
            ]);
            PublicationState::update($publication, [
                'status' => 'published',
                'question_id' => $question->id,
                'appliance_id' => $applianceId,
                'error_message' => null,
            ]);
            $this->audit($moderatorId, 'approve', ['message_id' => $record->telegram_message_id]);

            // Also respects a caller's outer transaction: never announce a rollback.
            DB::afterCommit(fn () => NotifyPublishedQuestion::run($queueId, (int) $question->id));

            return 'Вопрос опубликован: '.route('questions.show', ['question' => $question->slug]);
        });
    }

    private function cannotPublish(object $publication): ?string
    {
        if ($publication->status === 'published') {
            return "Вопрос уже опубликован: #{$publication->question_id}.";
        }
        if (! in_array($publication->status, ['pending_moderation', 'failed'], true)) {
            return "Нельзя опубликовать: текущий статус {$publication->status}.";
        }

        return null;
    }
}
