<?php

namespace App\Jobs;

use App\Enums\AnnouncementKind;
use App\Enums\AnnouncementStatus;
use App\Models\QuestionAnnouncement;
use App\Services\Announcements\QuestionAnnouncer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Отправляет один вопрос в один канал. Повторы с паузами: прокси или Telegram могут отвечать с задержкой. */
class SendQuestionAnnouncement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;

    public int $timeout = 60;

    /** @return list<int> */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function __construct(public int $announcementId) {}

    public function handle(QuestionAnnouncer $announcer): void
    {
        $token = (string) Str::uuid();
        $claimed = QuestionAnnouncement::query()->whereKey($this->announcementId)
            ->where('status', AnnouncementStatus::Pending->value)
            ->where(fn ($q) => $q->whereNull('processing_until')->orWhere('processing_until', '<=', now()))
            ->update(['processing_token' => $token, 'processing_until' => now()->addSeconds(120)]);
        if (! $claimed) {
            $until = QuestionAnnouncement::query()->whereKey($this->announcementId)
                ->where('status', AnnouncementStatus::Pending->value)->value('processing_until');
            if ($until && $this->job) {
                $this->release(max(1, now()->diffInSeconds(Carbon::parse($until), false) + 1));
            }

            return;
        }

        $announcement = QuestionAnnouncement::with('question.appliance')->find($this->announcementId);
        $channel = $announcement ? $announcer->channel($announcement->channel) : null;

        // отправлено раньше, вопрос удалён, канал выключен: делать нечего
        if (! $announcement || $announcement->status === AnnouncementStatus::Sent || ! $announcement->question || ! $channel?->enabled()) {
            QuestionAnnouncement::query()->whereKey($this->announcementId)->where('processing_token', $token)
                ->update(['processing_token' => null, 'processing_until' => null]);

            return;
        }

        try {
            $externalId = $announcement->kind === AnnouncementKind::Reminder
                ? $channel->remind($announcement->question, $this->originalMessageId($announcement))
                : $channel->send($announcement->question);
        } catch (\Throwable $e) {
            QuestionAnnouncement::query()->whereKey($this->announcementId)->where('processing_token', $token)
                ->update(['attempts' => DB::raw('attempts + 1'), 'last_error' => mb_substr($e->getMessage(), 0, 1000), 'processing_token' => null, 'processing_until' => null]);

            throw $e;
        }

        QuestionAnnouncement::query()->whereKey($this->announcementId)->where('processing_token', $token)->update([
            'status' => AnnouncementStatus::Sent,
            'attempts' => $announcement->attempts + 1,
            'last_error' => null,
            'external_id' => $externalId,
            'sent_at' => now(),
            'processing_token' => null,
            'processing_until' => null,
        ]);
    }

    /** Номер сообщения с самим вопросом в том же канале: на него отвечает напоминание. */
    private function originalMessageId(QuestionAnnouncement $reminder): ?string
    {
        $announcementId = QuestionAnnouncement::where([
            'question_id' => $reminder->question_id,
            'channel' => $reminder->channel,
            'kind' => AnnouncementKind::New,
            'status' => AnnouncementStatus::Sent,
        ])->value('external_id');

        if ($announcementId) {
            return (string) $announcementId;
        }

        $telegramRecord = DB::table('telegram_question_messages')
            ->where('question_id', $reminder->question_id)
            ->where('status', 'published')
            ->latest('id')
            ->first(['telegram_message_id', 'payload']);

        if (! $telegramRecord) {
            return null;
        }

        $payload = json_decode((string) $telegramRecord->payload, true) ?: [];

        return (string) (data_get($payload, 'published_message_id') ?: '');
    }

    public function failed(\Throwable $e): void
    {
        QuestionAnnouncement::whereKey($this->announcementId)->where('status', AnnouncementStatus::Pending->value)
            ->whereNull('processing_token')->update(['status' => AnnouncementStatus::Failed]);
    }
}
