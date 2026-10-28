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

/** Отправляет один вопрос в один канал. Повторы с паузами: прокси или Telegram могут отвечать с задержкой. */
class SendQuestionAnnouncement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;

    /** @return list<int> */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function __construct(public int $announcementId)
    {
    }

    public function handle(QuestionAnnouncer $announcer): void
    {
        $announcement = QuestionAnnouncement::with('question.appliance')->find($this->announcementId);
        $channel = $announcement ? $announcer->channel($announcement->channel) : null;

        // отправлено раньше, вопрос удалён, канал выключен: делать нечего
        if (! $announcement || $announcement->status === AnnouncementStatus::Sent || ! $announcement->question || ! $channel?->enabled()) {
            return;
        }

        try {
            $externalId = $announcement->kind === AnnouncementKind::Reminder
                ? $channel->remind($announcement->question, $this->originalMessageId($announcement))
                : $channel->send($announcement->question);
        } catch (\Throwable $e) {
            $announcement->update(['attempts' => $announcement->attempts + 1, 'last_error' => mb_substr($e->getMessage(), 0, 1000)]);

            throw $e;
        }

        $announcement->update([
            'status' => AnnouncementStatus::Sent,
            'attempts' => $announcement->attempts + 1,
            'last_error' => null,
            'external_id' => $externalId,
            'sent_at' => now(),
        ]);
    }

    /** Номер сообщения с самим вопросом в том же канале: на него отвечает напоминание. */
    private function originalMessageId(QuestionAnnouncement $reminder): ?string
    {
        return QuestionAnnouncement::where([
            'question_id' => $reminder->question_id,
            'channel' => $reminder->channel,
            'kind' => AnnouncementKind::New,
            'status' => AnnouncementStatus::Sent,
        ])->value('external_id');
    }

    public function failed(\Throwable $e): void
    {
        QuestionAnnouncement::whereKey($this->announcementId)->update(['status' => AnnouncementStatus::Failed]);
    }
}
