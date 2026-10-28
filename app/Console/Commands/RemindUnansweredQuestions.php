<?php

namespace App\Console\Commands;

use App\Enums\AnnouncementKind;
use App\Enums\AnnouncementStatus;
use App\Models\Question;
use App\Services\Announcements\QuestionAnnouncer;
use Illuminate\Console\Command;

/**
 * Один раз напоминает в чат о вопросе без ответа: не раньше чем через 48 часов и не позже чем через 7 дней после публикации.
 * Мастера отвечают добровольно, поэтому напоминание одно, текст мягкий, а за запуск уходит не больше нескольких сообщений.
 */
class RemindUnansweredQuestions extends Command
{
    /** Через сколько часов после публикации вопроса можно напомнить о нём. */
    public const MIN_AGE_HOURS = 48;

    /** Через сколько дней вопрос считается слишком старым для напоминания (старые попадают только в еженедельную сводку). */
    public const MAX_AGE_DAYS = 7;

    protected $signature = 'questions:remind
        {--dry-run : Только показать, о каких вопросах напомнили бы}
        {--limit=3 : Сколько напоминаний отправить за запуск}';

    protected $description = 'Напоминает в чат о вопросах без ответа (один раз, от 48 часов до 7 дней после публикации)';

    public function handle(QuestionAnnouncer $announcer): int
    {
        $channels = collect($announcer->channels())->filter(fn ($channel) => $channel->enabled() && $channel->canRemind())->keys()->all();

        if ($channels === []) {
            $this->line('Нет настроенных каналов, которые умеют напоминать.');

            return self::SUCCESS;
        }

        $questions = Question::query()
            ->unanswered()
            ->where('created_at', '<=', now()->subHours(self::MIN_AGE_HOURS))
            ->where('created_at', '>=', now()->subDays(self::MAX_AGE_DAYS))
            // напоминать есть к чему привязаться: исходное сообщение ушло в чат
            ->whereHas('announcements', fn ($q) => $q->whereIn('channel', $channels)->where('kind', AnnouncementKind::New)->where('status', AnnouncementStatus::Sent))
            ->whereDoesntHave('announcements', fn ($q) => $q->whereIn('channel', $channels)->where('kind', AnnouncementKind::Reminder))
            ->orderBy('created_at')
            ->limit(max(0, (int) $this->option('limit')))
            ->get();

        if ($questions->isEmpty()) {
            $this->line('Напоминать не о чем.');

            return self::SUCCESS;
        }

        foreach ($questions as $question) {
            $this->line(($this->option('dry-run') ? 'Напомнили бы: ' : 'Напоминаем: ') . "#{$question->id} {$question->title}");

            if (! $this->option('dry-run')) {
                $announcer->remind($question);
            }
        }

        return self::SUCCESS;
    }
}
