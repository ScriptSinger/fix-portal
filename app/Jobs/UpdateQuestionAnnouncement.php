<?php

namespace App\Jobs;

use App\Enums\AnnouncementStatus;
use App\Models\QuestionAnnouncement;
use App\Services\Announcements\QuestionAnnouncer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/** Приводит уже отправленное сообщение в чате в соответствие с отредактированным вопросом. */
class UpdateQuestionAnnouncement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @return list<int> */
    public function backoff(): array
    {
        return [60, 300];
    }

    public function __construct(public int $announcementId)
    {
    }

    public function handle(QuestionAnnouncer $announcer): void
    {
        $announcement = QuestionAnnouncement::with('question.appliance', 'question.user')->find($this->announcementId);
        $channel = $announcement ? $announcer->channel($announcement->channel) : null;

        // сообщения нет, оно ещё не отправлено (тогда уйдёт сразу с новым текстом), вопрос удалён или канал выключен
        if (! $announcement || $announcement->status !== AnnouncementStatus::Sent || ! $announcement->external_id || ! $announcement->question || ! $channel?->enabled() || ! $channel->canModify()) {
            return;
        }

        $channel->update($announcement->question, $announcement->external_id);
    }

    /** Сообщение остаётся как было: это не повод помечать отправку неудавшейся и слать вопрос в чат второй раз. */
    public function failed(\Throwable $e): void
    {
        Log::warning("Не удалось обновить сообщение в чате (отправка {$this->announcementId}): {$e->getMessage()}");
    }
}
