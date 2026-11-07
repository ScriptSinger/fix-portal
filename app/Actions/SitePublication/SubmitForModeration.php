<?php

namespace App\Actions\SitePublication;

use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class SubmitForModeration
{
    use AsAction, RecordsModerationActions;

    public function handle(int $id, int $moderatorId): string
    {
        if (! in_array($moderatorId, config('services.telegram.moderator_ids', []), true)) {
            return 'Нет доступа к отправке на модерацию.';
        }

        return DB::transaction(function () use ($id, $moderatorId): string {
            $record = DB::table('telegram_question_messages')->where('id', $id)->lockForUpdate()->first();
            if (! $record || (string) $record->telegram_chat_id !== (string) config('services.telegram.source_chat_id')) {
                return 'Вопрос недоступен для модерации.';
            }

            $publication = DB::table('telegram_question_publications')
                ->where('telegram_question_message_id', $id)->lockForUpdate()->first();
            if ($publication) {
                return 'Вопрос уже передан на модерацию.';
            }

            DB::table('telegram_question_publications')->insert([
                'telegram_question_message_id' => $id,
                'status' => 'pending_moderation',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('telegram_question_messages')->where('id', $id)->update([
                'status' => 'pending_moderation',
                'updated_at' => now(),
            ]);
            $this->audit($moderatorId, 'submit_for_moderation', ['queue_id' => $id]);

            return 'Сообщение передано на модерацию.';
        });
    }
}
