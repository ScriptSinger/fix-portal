<?php

namespace App\Services\Announcements;

use App\Enums\AnnouncementStatus;
use App\Jobs\RetractQuestionAnnouncement;
use App\Jobs\SendQuestionAnnouncement;
use App\Jobs\UpdateQuestionAnnouncement;
use App\Models\Question;
use App\Models\QuestionAnnouncement;
use App\Services\Announcements\Channels\AnnouncementChannel;
use App\Services\Announcements\Channels\TelegramChannel;
use App\Services\Announcements\Channels\VkChannel;
use Illuminate\Support\Facades\Log;

/** Ставит новый вопрос в очередь на отправку в каждый настроенный канал. Сбой здесь не мешает публикации вопроса. */
class QuestionAnnouncer
{
    /** @return array<string, AnnouncementChannel> по ключу канала */
    public function channels(): array
    {
        $telegram = app(TelegramChannel::class);
        $vk = app(VkChannel::class);

        return [$telegram->name() => $telegram, $vk->name() => $vk];
    }

    public function channel(string $name): ?AnnouncementChannel
    {
        return $this->channels()[$name] ?? null;
    }

    /** Пауза перед обновлением сообщения: серия быстрых правок превращается в один запрос. */
    public const EDIT_DELAY_SECONDS = 30;

    /** Вопрос отредактировали: уже отправленные сообщения в чатах обновляются. Неотправленные уйдут сразу с новым текстом. */
    public function refresh(Question $question): void
    {
        foreach ($this->channels() as $name => $channel) {
            if (! $channel->enabled()) {
                continue;
            }

            try {
                $id = QuestionAnnouncement::where(['question_id' => $question->id, 'channel' => $name, 'status' => AnnouncementStatus::Sent])->value('id');

                if ($id) {
                    UpdateQuestionAnnouncement::dispatch($id)->delay(now()->addSeconds(self::EDIT_DELAY_SECONDS))->afterCommit();
                }
            } catch (\Throwable $e) {
                Log::error("Не удалось поставить обновление вопроса {$question->id} в очередь канала {$name}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Вопрос удаляется: его сообщения в чатах убираются. Записи об отправках исчезнут вместе с вопросом,
     * поэтому в задачу уходят канал и номер сообщения, а не сама запись.
     */
    public function retract(Question $question): void
    {
        $sent = QuestionAnnouncement::where(['question_id' => $question->id, 'status' => AnnouncementStatus::Sent])->whereNotNull('external_id')->get(['channel', 'external_id']);

        foreach ($sent as $announcement) {
            try {
                if ($this->channel($announcement->channel)?->enabled()) {
                    RetractQuestionAnnouncement::dispatch($announcement->channel, $announcement->external_id)->afterCommit();
                }
            } catch (\Throwable $e) {
                Log::error("Не удалось поставить удаление сообщения вопроса {$question->id} в очередь канала {$announcement->channel}: {$e->getMessage()}");
            }
        }
    }

    /** Повторная отправка неудавшейся записи: счётчик попыток обнуляется, задача ставится заново. */
    public function retry(QuestionAnnouncement $announcement): bool
    {
        if ($announcement->status !== AnnouncementStatus::Failed) {
            return false;
        }

        $announcement->update(['status' => AnnouncementStatus::Pending, 'attempts' => 0, 'last_error' => null]);
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
                    ['status' => AnnouncementStatus::Pending],
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
