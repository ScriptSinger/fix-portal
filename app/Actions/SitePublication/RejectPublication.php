<?php

namespace App\Actions\SitePublication;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class RejectPublication
{
    use AsAction, RecordsModerationActions;

    public function handle(int $queueId, string $reason, ?int $moderatorId): string
    {
        return DB::transaction(function () use ($queueId, $reason, $moderatorId): string {
            $record = DB::table('telegram_question_messages')->where('id', $queueId)->lockForUpdate()->first();
            if (! $record) {
                return 'Сообщение для модерации не найдено.';
            }
            $publication = PublicationState::lockForMessage($record);
            if ($publication->status !== 'pending_moderation') {
                return "Нельзя отклонить: текущий статус {$publication->status}.";
            }

            PublicationState::update($publication, [
                'status' => 'rejected',
                'error_message' => $reason !== '' ? Str::limit($reason, 1000) : null,
            ]);
            $this->audit($moderatorId, 'reject', ['message_id' => $record->telegram_message_id, 'reason' => $reason]);

            return 'Вопрос отклонён.';
        });
    }
}
