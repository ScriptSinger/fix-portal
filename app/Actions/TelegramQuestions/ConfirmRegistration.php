<?php

namespace App\Actions\TelegramQuestions;

use App\Enums\TelegramQuestionConfirmationStatus;
use Illuminate\Support\Facades\DB;
use App\Telegram\QuestionInstructions;
use Lorisleiva\Actions\Concerns\AsAction;

class ConfirmRegistration
{
    use AsAction;

    /** @return array{message: string, confirmed: bool, edit: bool, delete?: bool} */
    public function handle(int $id, int $actor, int $chat, bool $confirm): array
    {
        return DB::transaction(function () use ($id, $actor, $chat, $confirm): array {
            $record = DB::table('telegram_question_messages')->where('id', $id)->lockForUpdate()->first();
            $result = ['confirmed' => false, 'edit' => false];
            if (! $record || (int) $record->telegram_user_id !== $actor || (int) $record->telegram_chat_id !== $chat) {
                return $result + ['message' => 'Подтвердить может только автор вопроса.'];
            }
            if ($record->community_confirmation_status === TelegramQuestionConfirmationStatus::Confirmed->value
                && $record->registered_at !== null && $record->archived_at === null) {
                return ['confirmed' => true, 'edit' => true, 'message' => QuestionInstructions::registered()];
            }
            if (! in_array($record->community_confirmation_status, [TelegramQuestionConfirmationStatus::Pending->value, TelegramQuestionConfirmationStatus::Expired->value], true)
                || $record->registered_at !== null || $record->archived_at !== null) {
                return $result + ['message' => 'Этот вопрос уже обработан.'];
            }
            if (! $confirm) {
                DB::table('telegram_question_messages')->where('id', $id)->update([
                    'community_confirmation_status' => TelegramQuestionConfirmationStatus::Declined->value, 'updated_at' => now(),
                ]);
                return ['confirmed' => false, 'edit' => true, 'delete' => true, 'message' => '❌ Вопрос не зарегистрирован.'];
            }
            RegisterQuestion::run($id);

            return ['confirmed' => true, 'edit' => true, 'message' => QuestionInstructions::registered()];
        });
    }
}
