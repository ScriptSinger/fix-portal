<?php

namespace App\Actions\TelegramQuestions;

use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class RegisterQuestion
{
    use AsAction;

    public function handle(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $record = DB::table('telegram_question_messages')->where('id', $id)->lockForUpdate()->first();
            if (! $record || $record->registered_at !== null || $record->archived_at !== null) {
                return;
            }
            DB::table('telegram_question_messages')->where('id', $id)->update([
                'registered_at' => now(), 'status' => 'pending_moderation', 'updated_at' => now(),
            ]);
            DB::table('telegram_question_publications')->insertOrIgnore([
                'telegram_question_message_id' => $id, 'status' => 'pending_moderation',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        });
    }
}
