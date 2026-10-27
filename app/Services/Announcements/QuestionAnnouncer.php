<?php

namespace App\Services\Announcements;

use App\Jobs\SendQuestionAnnouncement;
use App\Models\Question;
use App\Models\QuestionAnnouncement;
use App\Services\Announcements\Channels\AnnouncementChannel;
use App\Services\Announcements\Channels\TelegramChannel;
use Illuminate\Support\Facades\Log;

/** Ставит новый вопрос в очередь на отправку в каждый настроенный канал. Сбой здесь не мешает публикации вопроса. */
class QuestionAnnouncer
{
    /** @return array<string, AnnouncementChannel> по ключу канала */
    public function channels(): array
    {
        $telegram = app(TelegramChannel::class);

        return [$telegram->name() => $telegram];
    }

    public function channel(string $name): ?AnnouncementChannel
    {
        return $this->channels()[$name] ?? null;
    }

    /** Повторная отправка неудавшейся записи: счётчик попыток обнуляется, задача ставится заново. */
    public function retry(QuestionAnnouncement $announcement): bool
    {
        if ($announcement->status !== QuestionAnnouncement::FAILED) {
            return false;
        }

        $announcement->update(['status' => QuestionAnnouncement::PENDING, 'attempts' => 0, 'last_error' => null]);
        SendQuestionAnnouncement::dispatch($announcement->id);

        return true;
    }

    public function announce(Question $question): void
    {
        foreach ($this->channels() as $name => $channel) {
            if (! $channel->enabled()) {
                continue;
            }

            try {
                $announcement = QuestionAnnouncement::firstOrCreate(
                    ['question_id' => $question->id, 'channel' => $name],
                    ['status' => QuestionAnnouncement::PENDING],
                );

                if ($announcement->wasRecentlyCreated) {
                    SendQuestionAnnouncement::dispatch($announcement->id)->afterCommit();
                }
            } catch (\Throwable $e) {
                Log::error("Не удалось поставить вопрос {$question->id} в очередь канала {$name}: {$e->getMessage()}");
            }
        }
    }
}
