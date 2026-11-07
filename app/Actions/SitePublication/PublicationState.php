<?php

namespace App\Actions\SitePublication;

use Illuminate\Support\Facades\DB;

/** Publication decisions live here; message fields are only UI compatibility mirrors. */
class PublicationState
{
    public static function forMessage(object $message): ?object
    {
        return DB::table('telegram_question_publications')
            ->where('telegram_question_message_id', $message->id)->first();
    }

    /** Caller must hold the message row lock, including when creating the first decision. */
    public static function lockForMessage(object $message): ?object
    {
        return DB::table('telegram_question_publications')
            ->where('telegram_question_message_id', $message->id)->lockForUpdate()->first();
    }

    /** Caller holds both locks in a transaction. Never write registration or resolution fields. */
    public static function update(object $publication, array $changes): void
    {
        $values = array_replace([
            'status' => $publication->status,
            'question_id' => $publication->question_id,
            'appliance_id' => $publication->appliance_id,
            'error_message' => $publication->error_message,
        ], $changes, ['updated_at' => now()]);

        DB::table('telegram_question_publications')->where('id', $publication->id)->update($values);
        DB::table('telegram_question_messages')->where('id', $publication->telegram_question_message_id)->update($values);
    }
}
