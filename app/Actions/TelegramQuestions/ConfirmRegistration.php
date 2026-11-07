<?php

namespace App\Actions\TelegramQuestions;

use Illuminate\Support\Facades\DB;
use App\Telegram\QuestionInstructions;
use Lorisleiva\Actions\Concerns\AsAction;

class ConfirmRegistration
{
    use AsAction;

    /** @return array{message: string, confirmed: bool, edit: bool} */
    public function handle(int $id, int $actor, int $chat, bool $confirm): array
    {
        return DB::transaction(function () use ($id, $actor, $chat, $confirm): array {
            $record = DB::table('telegram_question_messages')->where('id', $id)->lockForUpdate()->first();
            $result = ['confirmed' => false, 'edit' => false];
            if (! $record || (int) $record->telegram_user_id !== $actor || (int) $record->telegram_chat_id !== $chat) {
                return $result + ['message' => 'Подтвердить может только автор вопроса.'];
            }
            if ($record->status !== 'awaiting_confirmation' || $record->registered_at !== null || $record->archived_at !== null) {
                return $result + ['message' => 'Этот вопрос уже обработан.'];
            }
            $expired = $record->created_at < now()->subMinutes(max(1, (int) config('services.telegram.question_confirmation_minutes', 10)))->toDateTimeString();
            if (! $confirm || $expired) {
                DB::table('telegram_question_messages')->where('id', $id)->update(['status' => 'cancelled', 'updated_at' => now()]);
                return ['confirmed' => false, 'edit' => true, 'message' => $expired ? '⌛ Срок подтверждения истёк.' : '❌ Вопрос не отправлен на модерацию.'];
            }
            RegisterQuestion::run($id);

            return ['confirmed' => true, 'edit' => true, 'message' => QuestionInstructions::registered()];
        });
    }
}
