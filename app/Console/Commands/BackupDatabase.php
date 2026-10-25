<?php

namespace App\Console\Commands;

use App\Services\Backup\DatabaseBackup;
use Illuminate\Console\Command;
use RuntimeException;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup {--label= : Пометка в имени файла, например before-merge}';

    protected $description = 'Снять резервную копию базы, проверить её, загрузить в облако и удалить устаревшие копии';

    /**
     * Запускается каждую ночь планировщиком (app/Console/Kernel.php) и вручную перед любой
     * массовой правкой данных: php artisan db:backup --label=что-делаем.
     */
    public function handle(DatabaseBackup $backup): int
    {
        try {
            $file = $backup->create($this->option('label'));
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Копия снята и проверена: ' . basename($file) . ' (' . $this->megabytes(filesize($file)) . ')');

        // Локальная копия остаётся, даже если облако недоступно, но запуск
        // считается неудачным, чтобы это попало в лог.
        try {
            $this->info('Загружена в облако: ' . $backup->upload($file));
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        foreach ($backup->prune(config('backup.keep_days')) as $deleted) {
            $this->line('Удалена старая копия на сервере: ' . basename($deleted));
        }

        foreach ($backup->pruneCloud(config('backup.cloud_keep_days')) as $deleted) {
            $this->line('Удалена старая копия в облаке: ' . $deleted);
        }

        return self::SUCCESS;
    }

    private function megabytes(int $bytes): string
    {
        return number_format($bytes / 1024 / 1024, 1, ',', ' ') . ' МБ';
    }
}
