<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Новые статьи, вопросы и прошивки попадают в sitemap.xml без деплоя
        $schedule->command('sitemap:generate')
            ->dailyAt('03:15')
            ->timezone('Asia/Yekaterinburg')
            ->withoutOverlapping()
            ->onFailure(fn () => Log::error('Не удалось обновить sitemap.xml: см. вывод sitemap:generate.'));

        // Ночная резервная копия базы: проверка, загрузка в облако, ротация (config/backup.php)
        $schedule->command('db:backup')
            ->dailyAt('02:30')
            ->timezone('Asia/Yekaterinburg')
            ->withoutOverlapping()
            ->onFailure(fn () => Log::critical('Ночная резервная копия базы не снята или не загружена в облако: см. вывод db:backup.'));

        // Не подтвердил почту за неделю и ничего не делал на сайте: это бот или брошенная регистрация, удаляем навсегда
        $schedule->command('users:prune-unverified --apply')
            ->dailyAt('04:00')
            ->timezone('Asia/Yekaterinburg')
            ->withoutOverlapping()
            ->onFailure(fn () => Log::error('Не удалось почистить неподтверждённых пользователей: см. вывод users:prune-unverified.'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
