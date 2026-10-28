<?php

namespace App\Services\Announcements;

use App\Enums\AnnouncementKind;
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
            if (! $channel->enabled() || ! $channel->canModify()) {
                continue;
            }

            try {
                // обновляется только сообщение о вопросе: текст напоминания не зависит от правок
                $id = QuestionAnnouncement::where(['question_id' => $question->id, 'channel' => $name, 'kind' => AnnouncementKind::New, 'status' => AnnouncementStatus::Sent])->value('id');

                if ($id) {
                    UpdateQuestionAnnouncement::dispatch($id)->delay(now()->addSeconds(self::EDIT_DELAY_SECONDS))->afterCommit();
                }
            } catch (\Throwable $e) {
                Log::error("Не удалось поставить обновление вопроса {$question->id} в очередь канала {$name}: {$e->getMessage()}");
            }
        }
    }

    /** Ставит напоминание о вопросе без ответа в очередь каналов, которые умеют напоминать. Возвращает, сколько отправок поставлено. */
    public function remind(Question $question): int
    {
        $queued = 0;

        foreach ($this->channels() as $name => $channel) {
            if (! $channel->enabled() || ! $channel->canRemind()) {
                continue;
            }

            $reminder = QuestionAnnouncement::firstOrCreate(
                ['question_id' => $question->id, 'channel' => $name, 'kind' => AnnouncementKind::Reminder],
                ['status' => AnnouncementStatus::Pending],
            );

            if ($reminder->wasRecentlyCreated) {
                SendQuestionAnnouncement::dispatch($reminder->id)->afterCommit();
                $queued++;
            }
        }

        return $queued;
    }

    /** Еженедельная сводка в каналы, которые умеют её слать. Сбой одного канала не мешает остальным. */
    public function digest(int $count, string $url): void
    {
        foreach ($this->channels() as $name => $channel) {
            if (! $channel->enabled() || ! $channel->canRemind()) {
                continue;
            }

            try {
                $channel->digest($count, $url);
            } catch (\Throwable $e) {
                Log::error("Не удалось отправить сводку в канал {$name}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Вопрос удаляется: его сообщения в чатах убираются (и объявление, и напоминание). Записи об отправках исчезнут вместе с вопросом,
     * поэтому в задачу уходят канал и номер сообщения, а не сама запись.
     */
    public function retract(Question $question): void
    {
        $sent = QuestionAnnouncement::where(['question_id' => $question->id, 'status' => AnnouncementStatus::Sent])->whereNotNull('external_id')->get(['channel', 'external_id']);

        foreach ($sent as $announcement) {
            try {
                $channel = $this->channel($announcement->channel);

                if ($channel?->enabled() && $channel->canModify()) {
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

    /** Ставит вопрос в очередь на отправку в каждый настроенный канал, где его ещё не было. Возвращает, сколько отправок поставлено. */
    public function announce(Question $question): int
    {
        $queued = 0;

        foreach ($this->channels() as $name => $channel) {
            if (! $channel->enabled()) {
                continue;
            }

            try {
                $announcement = QuestionAnnouncement::firstOrCreate(
                    ['question_id' => $question->id, 'channel' => $name, 'kind' => AnnouncementKind::New],
                    ['status' => AnnouncementStatus::Pending],
                );

                if ($announcement->wasRecentlyCreated) {
                    SendQuestionAnnouncement::dispatch($announcement->id)->afterCommit();
                    $queued++;
                }
            } catch (\Throwable $e) {
                Log::error("Не удалось поставить вопрос {$question->id} в очередь канала {$name}: {$e->getMessage()}");
            }
        }

        return $queued;
    }
}
