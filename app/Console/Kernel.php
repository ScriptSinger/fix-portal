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
