<?php

namespace App\Actions\SitePublication;

use App\Models\Question;
use App\Services\Announcements\QuestionAnnouncer;
use App\Services\TelegramPhotoImporter;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class AppendToPublishedQuestion
{
    use AsAction, RecordsModerationActions;

    public function __construct(
        private readonly TelegramPhotoImporter $photos,
        private readonly QuestionAnnouncer $announcer,
    ) {}

    public function handle(int $queueId, array $payload, ?int $moderatorId): string
    {
        // Downloads precede locks; deduplication is checked again under the row lock.
        try {
            $files = $this->photos->prepare($queueId, $payload);
        } catch (\Throwable $e) {
            report($e);

            return 'Не удалось скачать вложение из Telegram. Проверьте proxy и повторите команду.';
        }
        $text = trim((string) data_get($payload, 'message.text', data_get($payload, 'message.caption', '')));
        if ($files === [] && $text === '') {
            return 'В ответном сообщении нет текста или фото.';
        }

        return DB::transaction(function () use ($queueId, $payload, $moderatorId, $files, $text): string {
            $record = DB::table('telegram_question_messages')->where('id', $queueId)->lockForUpdate()->first();
            if (! $record) {
                return 'Запись очереди не найдена.';
            }
            $publication = PublicationState::lockForMessage($record);
            if (! $publication || $publication->status !== 'published') {
                return 'К вопросу с текущим статусом нельзя добавлять сообщения.';
            }
            $storedPayload = json_decode((string) $record->payload, true) ?: [];
            $messageId = (int) data_get($payload, 'message.message_id');
            $attachedIds = array_map('intval', (array) ($storedPayload['attached_message_ids'] ?? []));
            if ($messageId > 0 && in_array($messageId, $attachedIds, true)) {
                return 'Это сообщение уже прикреплено к вопросу.';
            }
            $question = Question::query()->lockForUpdate()->find($publication->question_id);
            if (! $question) {
                return 'Опубликованный вопрос не найден.';
            }
            $images = $this->photos->persist($files);
            if ($text !== '') {
                $question->description .= "\n\n".e($text);
            }
            foreach ($images as $image) {
                $question->description .= '<img src="'.e($image['url']).'" class="img-fluid" data-id="'.$image['id'].'">';
            }
            $question->save();
            if ($messageId > 0) {
                $storedPayload['attached_message_ids'] = [...$attachedIds, $messageId];
                DB::table('telegram_question_messages')->where('id', $queueId)->update([
                    'payload' => json_encode($storedPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                    'updated_at' => now(),
                ]);
            }
            $this->audit($moderatorId, 'attach', [
                'queue_id' => $queueId, 'message_id' => $messageId,
                'images' => count($images), 'has_text' => $text !== '',
            ]);
            DB::afterCommit(function () use ($question): void {
                try {
                    $this->announcer->refresh($question);
                } catch (\Throwable $e) {
                    report($e);
                }
            });

            return 'Сообщение и вложения добавлены к опубликованному вопросу.';
        });
    }
}
