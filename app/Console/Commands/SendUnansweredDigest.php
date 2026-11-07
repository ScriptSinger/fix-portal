<?php

namespace App\Console\Commands;

use App\Models\Question;
use App\Services\Announcements\Channels\TelegramChannel;
use App\Services\Announcements\QuestionAnnouncer;
use App\Services\TelegramBotApi;
use App\Services\TelegramCommunityDigest;
use Illuminate\Console\Command;

/** Раз в неделю одним сообщением сообщает в чат, сколько вопросов ждут ответа, со ссылкой на их список (старые вопросы только считаются). */
class SendUnansweredDigest extends Command
{
    protected $signature = 'questions:digest {--dry-run : Только показать текст, ничего не отправлять}';

    protected $description = 'Отправляет в чат сводку о количестве вопросов без ответа';

    public function handle(QuestionAnnouncer $announcer, TelegramCommunityDigest $community, TelegramBotApi $telegram): int
    {
        $count = Question::unanswered()->count();

        $telegramText = $community->text();

        if ($count === 0 && $telegramText === null) {
            $this->line('Вопросов без ответа нет, сводка не нужна.');

            return self::SUCCESS;
        }

        $url = route('questions.index', ['status' => 'unanswered']);

        if ($this->option('dry-run')) {
            // точный текст, который уйдёт в чат
            $this->line('В чат ушло бы:');
            $this->line(app(TelegramChannel::class)->digestText($count, $url));
            if ($telegramText !== null) {
                $this->line(strip_tags($telegramText));
            }

            return self::SUCCESS;
        }

        if ($telegramText !== null && config('services.telegram.bot_token') && config('services.telegram.source_chat_id')) {
            $siteText = $count > 0 ? app(TelegramChannel::class)->digestText($count, $url) : '';
            $telegram->call('sendMessage', [
                'chat_id' => (int) config('services.telegram.source_chat_id'),
                'text' => trim($siteText."\n\n".$telegramText),
                'parse_mode' => 'HTML',
            ]);
            if ($count > 0) {
                $announcer->channel('vk')?->digest($count, $url);
            }
        } elseif ($count > 0) {
            $announcer->digest($count, $url);
        }
        $this->info("Сводка отправлена: {$count}");

        return self::SUCCESS;
    }
}
