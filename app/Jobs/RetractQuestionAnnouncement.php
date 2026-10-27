<?php

namespace App\Jobs;

use App\Services\Announcements\QuestionAnnouncer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/** Убирает из чата сообщение об удалённом вопросе. Запись об отправке к этому моменту уже стёрта, поэтому в задаче канал и номер. */
class RetractQuestionAnnouncement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @return list<int> */
    public function backoff(): array
    {
        return [60, 300];
    }

    public function __construct(public string $channel, public string $externalId)
    {
    }

    public function handle(QuestionAnnouncer $announcer): void
    {
        $channel = $announcer->channel($this->channel);

        if ($channel?->enabled() && $channel->canModify()) {
            $channel->retract($this->externalId);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::warning("Не удалось удалить сообщение {$this->externalId} из канала {$this->channel}: {$e->getMessage()}");
    }
}
