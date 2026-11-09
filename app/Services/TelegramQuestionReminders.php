<?php

namespace App\Services;

use App\Enums\TelegramQuestionResolutionStatus;
use App\Jobs\SendTelegramQuestionReminder;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TelegramQuestionReminders
{
    public const INTERVAL_HOURS = 72;

    public const MAX_REMINDERS = 3;

    // Longer than a worker's execution timeout. Row locks also serialize recovery
    // with an in-flight send; a delayed original job may safely use the same row.
    public const PENDING_TIMEOUT_MINUTES = 10;

    public function enabled(): bool
    {
        return trim((string) config('services.telegram.bot_token')) !== ''
            && (int) config('services.telegram.source_chat_id') !== 0;
    }

    /** Publication/moderation status deliberately does not affect registration. */
    public function eligible(): Builder
    {
        $query = DB::table('telegram_question_messages')
            ->where('telegram_chat_id', (int) config('services.telegram.source_chat_id'))
            ->whereNotNull('registered_at')
            ->where('registered_at', '<=', now()->subHours(self::INTERVAL_HOURS))
            ->where('resolution_status', TelegramQuestionResolutionStatus::Open->value)
            ->whereNull('archived_at');

        // Match the intake policy: 0/1 means no specific topic restriction.
        $threadId = (int) config('services.telegram.source_thread_id');
        if ($threadId > 1) {
            $query->where('telegram_thread_id', $threadId);
        }

        return $query;
    }

    public function due(): Builder
    {
        return $this->eligible()
            ->whereNotExists(fn (Builder $query) => $query->selectRaw('1')
                ->from('telegram_question_reminders')
                ->whereColumn('telegram_question_message_id', 'telegram_question_messages.id')
                ->where('status', 'pending')
                ->where('updated_at', '>', now()->subMinutes(self::PENDING_TIMEOUT_MINUTES)))
            ->whereNotExists(fn (Builder $query) => $query->selectRaw('1')
                ->from('telegram_question_reminders')
                ->whereColumn('telegram_question_message_id', 'telegram_question_messages.id')
                ->where('status', 'sent')
                ->where(function (Builder $query): void {
                    $query->where('number', '>=', self::MAX_REMINDERS)
                        ->orWhereNull('sent_at')
                        ->orWhere('sent_at', '>', now()->subHours(self::INTERVAL_HOURS));
                }));
    }

    public function reserve(int $questionId): ?int
    {
        return DB::transaction(function () use ($questionId): ?int {
            if (! $this->enabled()) {
                return null;
            }

            // All reservations and sends lock the question first, then its reminders.
            $question = $this->eligible()->where('id', $questionId)->lockForUpdate()->first();
            if (! $question) {
                return null;
            }

            $reminders = DB::table('telegram_question_reminders')
                ->where('telegram_question_message_id', $questionId)
                ->lockForUpdate()->get();

            if ($reminders->contains(fn ($reminder) => $reminder->status === 'pending'
                && $reminder->updated_at !== null
                && Carbon::parse($reminder->updated_at)->gt(now()->subMinutes(self::PENDING_TIMEOUT_MINUTES)))) {
                return null;
            }

            $sent = $reminders->where('status', 'sent');
            $number = (int) $sent->max('number') + 1;
            if ($number > self::MAX_REMINDERS || $sent->contains(fn ($reminder) => $reminder->sent_at === null
                || Carbon::parse($reminder->sent_at)->gt(now()->subHours(self::INTERVAL_HOURS)))) {
                return null;
            }

            $existing = $reminders->firstWhere('number', $number);
            $values = ['status' => 'pending', 'last_error' => null, 'updated_at' => now()];
            if ($existing) {
                DB::table('telegram_question_reminders')->where('id', $existing->id)->update($values);

                return (int) $existing->id;
            }

            return DB::table('telegram_question_reminders')->insertGetId($values + [
                'telegram_question_message_id' => $questionId,
                'number' => $number,
                'created_at' => now(),
            ]);
        });
    }

    public function queue(int $questionId): bool
    {
        $reminderId = $this->reserve($questionId);
        if ($reminderId === null) {
            return false;
        }

        try {
            SendTelegramQuestionReminder::dispatch($reminderId)->afterCommit();
        } catch (\Throwable $exception) {
            DB::table('telegram_question_reminders')->where('id', $reminderId)
                ->where('status', 'pending')->update([
                    'status' => 'failed',
                    'last_error' => mb_substr($exception->getMessage(), 0, 1000),
                    'updated_at' => now(),
                ]);

            throw $exception;
        }

        return true;
    }
}
