<?php

namespace App\Actions\SitePublication;

use App\Models\Question;
use App\Services\TelegramBotApi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

/** Called after the publication commits; Telegram failure cannot unpublish a question. */
class NotifyPublishedQuestion
{
    use AsAction;

    public function __construct(private readonly TelegramBotApi $telegram) {}

    public function handle(int $queueId, int $questionId): void
    {
        $record = DB::table('telegram_question_messages')->where('id', $queueId)->first();
        $question = Question::find($questionId);
        if (! $record || ! $question) {
            return;
        }
        $payload = json_decode((string) $record->payload, true) ?: [];
        $threadId = (int) data_get($payload, 'message.message_thread_id');
        $error = null;
        $published = [];
        try {
            $published = $this->telegram->call('sendMessage', array_filter([
                'chat_id' => (int) $record->telegram_chat_id,
                'text' => "Вопрос опубликован на Ufamasters:\n".route('questions.show', ['question' => $question->slug]),
                'reply_to_message_id' => (int) $record->telegram_message_id,
                'message_thread_id' => $threadId > 1 ? $threadId : null,
                'disable_web_page_preview' => false,
            ], static fn (mixed $value): bool => $value !== null));
        } catch (\Throwable $e) {
            $error = 'Вопрос опубликован, но ссылка не отправлена в Telegram: '.Str::limit($e->getMessage(), 900);
        }

        DB::transaction(function () use ($queueId, $questionId, $published, $error): void {
            $record = DB::table('telegram_question_messages')->where('id', $queueId)->lockForUpdate()->first();
            if (! $record) {
                return;
            }
            $publication = PublicationState::lockForMessage($record);
            if ($publication->status !== 'published' || (int) $publication->question_id !== $questionId) {
                return;
            }
            PublicationState::update($publication, ['error_message' => $error]);
            if ($error === null) {
                // Merge into the latest payload, preserving concurrent attachments.
                $payload = json_decode((string) $record->payload, true) ?: [];
                $payload['published_message_id'] = (int) ($published['message_id'] ?? 0) ?: null;
                DB::table('telegram_question_messages')->where('id', $queueId)->update([
                    'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
