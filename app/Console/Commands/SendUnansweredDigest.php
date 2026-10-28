<?php

namespace App\Console\Commands;

use App\Models\Question;
use App\Services\Announcements\Channels\TelegramChannel;
use App\Services\Announcements\QuestionAnnouncer;
use Illuminate\Console\Command;

/** Раз в неделю одним сообщением сообщает в чат, сколько вопросов ждут ответа, со ссылкой на их список (старые вопросы только считаются). */
class SendUnansweredDigest extends Command
{
    protected $signature = 'questions:digest {--dry-run : Только показать текст, ничего не отправлять}';

    protected $description = 'Отправляет в чат сводку о количестве вопросов без ответа';

    public function handle(QuestionAnnouncer $announcer): int
    {
        $count = Question::unanswered()->count();

        if ($count === 0) {
            $this->line('Вопросов без ответа нет, сводка не нужна.');

            return self::SUCCESS;
        }

        $url = route('questions.index', ['status' => 'unanswered']);

        if ($this->option('dry-run')) {
            // точный текст, который уйдёт в чат
            $this->line('В чат ушло бы:');
            $this->line(app(TelegramChannel::class)->digestText($count, $url));

            return self::SUCCESS;
        }

        $announcer->digest($count, $url);
        $this->info("Сводка отправлена: {$count}");

        return self::SUCCESS;
    }
}
