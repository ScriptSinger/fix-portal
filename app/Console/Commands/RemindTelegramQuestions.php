<?php

namespace App\Console\Commands;

use App\Services\TelegramQuestionReminders;
use Illuminate\Console\Command;

class RemindTelegramQuestions extends Command
{
    protected $signature = 'telegram:questions-remind
        {--dry-run : Только показать, о каких Telegram-вопросах напомнили бы}
        {--limit=3 : Сколько напоминаний поставить в очередь за запуск}';

    protected $description = 'Напоминает об открытых Telegram-вопросах каждые 72 часа, не более трёх раз';

    public function handle(TelegramQuestionReminders $reminders): int
    {
        if (! $reminders->enabled()) {
            $this->line('Не настроен Telegram-бот или исходный чат.');

            return self::SUCCESS;
        }

        $limit = max(0, (int) $this->option('limit'));
        if ($limit === 0) {
            return self::SUCCESS;
        }

        $count = 0;
        // Use a stable cursor order: reserving a row removes it from the due query.
        foreach ($reminders->due()->orderBy('registered_at')->orderBy('id')->cursor() as $question) {
            if ($this->option('dry-run') || $reminders->queue((int) $question->id)) {
                $this->line(($this->option('dry-run') ? 'Напомнили бы: ' : 'В очереди: ')."#{$question->id}");
                if (++$count >= $limit) {
                    break;
                }
            }
        }

        if ($count === 0) {
            $this->line('Напоминать не о чем.');
        }

        return self::SUCCESS;
    }
}
