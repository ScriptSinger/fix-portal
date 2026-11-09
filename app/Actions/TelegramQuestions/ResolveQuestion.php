<?php

namespace App\Actions\TelegramQuestions;

use App\Enums\TelegramQuestionResolutionStatus;
use App\Jobs\SyncTelegramMemberTag;
use App\Jobs\SendTelegramSolutionAcknowledgement;
use App\Services\TelegramReputation;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class ResolveQuestion
{
    use AsAction;

    /** @param array<string, mixed> $message */
    public function handle(array $message, ?int $questionId = null, bool $acknowledge = false): string
    {
        $chatId = (int) data_get($message, 'chat.id');
        $threadId = (int) data_get($message, 'message_thread_id', 0);
        // Intake stores Telegram's General topic (1) and absent topics as 0.
        $threadId = $threadId > 1 ? $threadId : 0;
        $authorId = (int) data_get($message, 'from.id');
        $reply = data_get($message, 'reply_to_message');
        $answererId = (int) data_get($reply, 'from.id');
        $answerMessageId = (int) data_get($reply, 'message_id');
        $answererName = trim(implode(' ', array_filter([
            trim((string) data_get($reply, 'from.first_name', '')),
            trim((string) data_get($reply, 'from.last_name', '')),
        ])));
        $answererName = $answererName !== '' ? mb_strimwidth($answererName, 0, 80, '…') : 'ID '.$answererId;

        if ($chatId === 0 || $authorId <= 0 || ! is_array($reply) || $answerMessageId <= 0) {
            return 'Ответьте командой /solved на сообщение участника, который помог решить вопрос.';
        }

        if ($answererId <= 0 || $answererId === $authorId || data_get($reply, 'from.is_bot') === true
            || data_get($message, 'from.is_bot') === true || isset($reply['sender_chat']) || isset($message['sender_chat'])) {
            return 'Нужно ответить /solved именно на сообщение участника с решением.';
        }

        $answerThreadId = (int) data_get($reply, 'message_thread_id', $threadId);
        $answerThreadId = $answerThreadId > 1 ? $answerThreadId : 0;
        if ((int) data_get($reply, 'chat.id', $chatId) !== $chatId || $answerThreadId !== $threadId) {
            return 'Сообщение с решением должно быть из того же чата и темы.';
        }

        try {
            return DB::transaction(function () use ($message, $questionId, $chatId, $threadId, $authorId, $answererId, $answerMessageId, $answererName, $acknowledge): string {
                // Publication/moderation status does not govern Telegram resolution.
                $questions = DB::table('telegram_question_messages')
                    ->where('telegram_chat_id', $chatId)
                    ->where('telegram_thread_id', $threadId)
                    ->where('telegram_user_id', $authorId)
                    ->whereNotNull('registered_at')
                    ->whereNull('archived_at')
                    ->where('resolution_status', TelegramQuestionResolutionStatus::Open->value)
                    ->when($questionId !== null, fn ($query) => $query->where('id', $questionId))
                    ->orderBy('id')
                    ->limit(2)
                    ->lockForUpdate()
                    ->get();

                if ($questions->isEmpty()) {
                    return 'У вас нет открытого Telegram-вопроса для закрытия в этой теме.';
                }

                if ($questions->count() > 1) {
                    return 'У вас несколько открытых вопросов в этой теме. Укажите ID: /solved <ID>, ответив на сообщение с решением.';
                }

                $question = $questions->first();
                $award = DB::table('telegram_solution_awards')
                    ->where('telegram_question_message_id', $question->id)
                    ->orWhere(fn ($query) => $query->where('telegram_chat_id', $chatId)->where('telegram_message_id', $answerMessageId))
                    ->lockForUpdate()
                    ->first();

                if ($award) {
                    return 'Этот вопрос или сообщение с решением уже учтены в карме.';
                }

                $now = now();
                DB::table('telegram_solution_awards')->insert([
                    'telegram_question_message_id' => $question->id,
                    'telegram_chat_id' => $chatId,
                    'telegram_user_id' => $answererId,
                    'telegram_message_id' => $answerMessageId,
                    'command_message_id' => data_get($message, 'message_id'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('telegram_question_messages')->where('id', $question->id)->update([
                    'resolution_status' => TelegramQuestionResolutionStatus::Solved->value,
                    'resolved_at' => $now,
                    'solution_telegram_user_id' => $answererId,
                    'solution_telegram_message_id' => $answerMessageId,
                    'updated_at' => $now,
                ]);

                if ($acknowledge) {
                    SendTelegramSolutionAcknowledgement::dispatch($chatId, $answerMessageId);
                }
                DB::afterCommit(fn () => SyncTelegramMemberTag::dispatch($chatId, $answererId));

                $count = app(TelegramReputation::class)->countFor($answererId);
                $rank = TelegramReputation::rank($count);

                return "Вопрос отмечен решённым. {$answererName} — {$rank}, карма: {$count}.";
            }, 3);
        } catch (UniqueConstraintViolationException $e) {
            // Concurrent resolutions of different questions may race on the answer key.
            // The failed transaction rolls back both the award and the solved state.
            return 'Этот вопрос или сообщение с решением уже учтены в карме.';
        }
    }
}
