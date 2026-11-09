<?php

namespace App\Jobs;

use App\Enums\TelegramQuestionConfirmationStatus;
use App\Services\TelegramPromptDeletion;
use App\Services\TelegramRegistrationPrompt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ExpireTelegramQuestionPrompt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(public int $questionId, public int $promptMessageId) {}

    public function backoff(): array
    {
        return [60, 300, 900, 3600];
    }

    public function handle(TelegramPromptDeletion $deletion): void
    {
        $outcome = DB::transaction(function (): array {
            $record = DB::table('telegram_question_messages')->where('id', $this->questionId)->lockForUpdate()->first();
            if (! $record || (int) $record->confirmation_prompt_message_id !== $this->promptMessageId) {
                return [];
            }
            $status = $record->community_confirmation_status;
            if ($status === TelegramQuestionConfirmationStatus::Pending->value) {
                if ($record->confirmation_expires_at === null) {
                    return [];
                }
                $expiresAt = Carbon::parse($record->confirmation_expires_at);
                if (now()->lt($expiresAt)) {
                    return ['delay' => max(1, $expiresAt->getTimestamp() - now()->getTimestamp())];
                }
                DB::table('telegram_question_messages')->where('id', $record->id)->update([
                    'community_confirmation_status' => TelegramQuestionConfirmationStatus::Expired->value,
                    'updated_at' => now(),
                ]);
                $status = TelegramQuestionConfirmationStatus::Expired->value;
            }
            if (! in_array($status, [TelegramQuestionConfirmationStatus::Declined->value, TelegramQuestionConfirmationStatus::Expired->value, TelegramQuestionConfirmationStatus::Confirmed->value], true)) {
                return [];
            }

            return ['chat_id' => (int) $record->telegram_chat_id,
                'confirmed' => $status === TelegramQuestionConfirmationStatus::Confirmed->value];
        });

        if (isset($outcome['delay'])) {
            $this->release($outcome['delay']);
            return;
        }
        if (! isset($outcome['chat_id'])) {
            return;
        }
        if ($outcome['confirmed']) {
            app(TelegramRegistrationPrompt::class)->confirm($this->questionId, $outcome['chat_id'], $this->promptMessageId);
            return;
        }
        $deletion->delete($outcome['chat_id'], $this->promptMessageId);
        DB::table('telegram_question_messages')->where('id', $this->questionId)
            ->where('confirmation_prompt_message_id', $this->promptMessageId)
            ->update(['confirmation_prompt_message_id' => null, 'updated_at' => now()]);
    }
}
