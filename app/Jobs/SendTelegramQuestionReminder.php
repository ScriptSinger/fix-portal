<?php

namespace App\Jobs;

use App\Services\TelegramBotApi;
use App\Services\TelegramQuestionReminders;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SendTelegramQuestionReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Failed rows are retried by the reminder command using the same number.
    public int $tries = 1;

    public function __construct(public int $reminderId) {}

    public function handle(TelegramBotApi $telegram, TelegramQuestionReminders $reminders): void
    {
        $questionId = DB::table('telegram_question_reminders')
            ->where('id', $this->reminderId)->value('telegram_question_message_id');
        if ($questionId === null) {
            return;
        }

        $error = DB::transaction(function () use ($questionId, $telegram, $reminders): ?\Throwable {
            $question = DB::table('telegram_question_messages')->where('id', $questionId)->lockForUpdate()->first();
            $reminder = DB::table('telegram_question_reminders')->where('id', $this->reminderId)->lockForUpdate()->first();
            if (! $reminder || $reminder->status !== 'pending') {
                return null;
            }

            if (! $question || ! $reminders->enabled() || ! $reminders->eligible()->where('id', $questionId)->exists()) {
                $this->update(['status' => 'cancelled', 'last_error' => null]);

                return null;
            }

            $sent = DB::table('telegram_question_reminders')->where('telegram_question_message_id', $questionId)
                ->where('status', 'sent')->lockForUpdate()->get();
            if ((int) $reminder->number !== (int) $sent->max('number') + 1
                || (int) $reminder->number > TelegramQuestionReminders::MAX_REMINDERS
                || $sent->contains(fn ($row) => $row->sent_at === null
                    || Carbon::parse($row->sent_at)->gt(now()->subHours(TelegramQuestionReminders::INTERVAL_HOURS)))) {
                $this->update(['status' => 'cancelled', 'last_error' => null]);

                return null;
            }

            $payload = [
                'chat_id' => (int) $question->telegram_chat_id,
                'text' => "Вопрос #{$question->id} пока открыт. Если можете помочь, ответьте, пожалуйста.\n"
                    ."Автор: если ответ помог, поставьте 🤝 на сообщение помощника и подтвердите, какой вопрос решён.",
                'reply_parameters' => ['message_id' => (int) $question->telegram_message_id],
            ];
            if ((int) $question->telegram_thread_id > 0) {
                $payload['message_thread_id'] = (int) $question->telegram_thread_id;
            }

            // Keep the row locks through HTTP so duplicate workers cannot both send.
            // Do not retry the transaction: Telegram does not provide an idempotency key.
            try {
                $result = $telegram->call('sendMessage', $payload);
                if ((int) ($result['message_id'] ?? 0) <= 0) {
                    throw new \RuntimeException('Telegram reminder response has no message_id.');
                }
            } catch (\Throwable $exception) {
                $this->update(['status' => 'failed', 'last_error' => mb_substr($exception->getMessage(), 0, 1000)]);

                return $exception;
            }

            $this->update([
                'status' => 'sent',
                'telegram_message_id' => (int) $result['message_id'],
                'sent_at' => now(),
                'last_error' => null,
            ]);

            return null;
        });

        // Commit failure details before notifying the queue worker.
        if ($error) {
            throw $error;
        }
    }

    public function failed(?\Throwable $exception): void
    {
        DB::table('telegram_question_reminders')->where('id', $this->reminderId)
            ->where('status', 'pending')->update([
                'status' => 'failed',
                'last_error' => mb_substr($exception?->getMessage() ?? 'Reminder job failed.', 0, 1000),
                'updated_at' => now(),
            ]);
    }

    private function update(array $values): void
    {
        DB::table('telegram_question_reminders')->where('id', $this->reminderId)
            ->update($values + ['updated_at' => now()]);
    }
}
