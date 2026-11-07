<?php

namespace App\Console\Commands;

use App\Enums\AnnouncementKind;
use App\Enums\AnnouncementStatus;
use App\Models\Question;
use App\Services\Announcements\QuestionAnnouncer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Напоминает в чат о вопросе без ответа каждые 72 часа, но не более трёх раз.
 * Мастера отвечают добровольно, поэтому текст мягкий, а запуск ограничен по количеству сообщений.
 */
class RemindUnansweredQuestions extends Command
{
    protected $signature = 'questions:remind
        {--dry-run : Только показать, о каких вопросах напомнили бы}
        {--limit=3 : Сколько напоминаний отправить за запуск}';

    protected $description = 'Напоминает в чат о вопросах без ответа каждые 72 часа (не более трёх раз)';

    public function handle(QuestionAnnouncer $announcer): int
    {
        $channels = collect($announcer->channels())->filter(fn ($channel) => $channel->enabled() && $channel->canRemind())->keys()->all();

        if ($channels === []) {
            $this->line('Нет настроенных каналов, которые умеют напоминать.');

            return self::SUCCESS;
        }

        $questions = Question::query()
            ->unanswered()
            ->where('created_at', '<=', now()->subHours(QuestionAnnouncer::REMINDER_INTERVAL_HOURS))
            ->where(function ($query) use ($channels): void {
                $query
                    // Обычные вопросы сайта имеют объявление о публикации.
                    ->whereHas('announcements', fn ($q) => $q->whereIn('channel', $channels)->where('kind', AnnouncementKind::New)->where('status', AnnouncementStatus::Sent))
                    // Telegram-вопросы публикуются отдельным moderation-сервисом и
                    // уже регистрируются в telegram_question_messages.
                    ->orWhereExists(fn ($q) => $q->select(DB::raw('1'))
                        ->from('telegram_question_messages')
                        ->whereColumn('telegram_question_messages.question_id', 'questions.id')
                        ->where('telegram_question_messages.status', 'published'));
            })
            ->whereDoesntHave('announcements', fn ($q) => $q->whereIn('channel', $channels)->where('kind', AnnouncementKind::Reminder))
            ->orderBy('created_at')
            ->get();

        if ($questions->isEmpty()) {
            $this->line('Напоминать не о чем.');

            return self::SUCCESS;
        }

        $queued = 0;
        foreach ($questions as $question) {
            if ($queued >= max(0, (int) $this->option('limit'))) {
                break;
            }

            $this->line(($this->option('dry-run') ? 'Напомнили бы: ' : 'Напоминаем: ')."#{$question->id} {$question->title}");

            if (! $this->option('dry-run')) {
                $queued += $announcer->remind($question);
            }
        }

        return self::SUCCESS;
    }
}
