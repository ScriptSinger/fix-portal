<?php

namespace App\Console\Commands;

use App\Models\Firmware;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeLocalFirmwareFiles extends Command
{
    protected $signature = 'firmware:purge-local
        {--apply : Удалить файлы (без флага только список)}
        {--disk=firmware_s3 : Диск, где должна лежать копия}';

    protected $description = 'Удаляет локальные файлы прошивок, у которых все записи уже перенесены в облако и копия там подтверждена';

    public function handle(): int
    {
        $local = Storage::disk('public');
        $cloud = Storage::disk((string) $this->option('disk'));
        $dir = $local->path('firmwares');

        // Имена файлов, которые ещё нужны записям на локальном диске
        $stillNeeded = Firmware::query()
            ->whereNull('file_disk')
            ->whereNotNull('file_path')
            ->pluck('file_path')
            ->flip();

        // Имена файлов, чьи записи перенесены: у них должна быть копия в облаке
        $migratedKeys = Firmware::query()
            ->where('file_disk', $this->option('disk'))
            ->pluck('file_path');
        $md5ToKey = $migratedKeys->mapWithKeys(fn ($key) => [basename($key) => $key]);

        $deletable = [];
        $keep = ['needed' => 0, 'no_cloud_copy' => 0];
        $bytes = 0;

        foreach (scandir($dir) as $name) {
            $path = "{$dir}/{$name}";

            if (! is_file($path)) {
                continue;
            }

            if (isset($stillNeeded[$name])) {
                $keep['needed']++;

                continue;
            }

            // Файл больше не нужен ни одной локальной записи: удаляем, только если его содержимое есть в облаке
            $md5 = md5_file($path);
            $key = $md5ToKey[$md5] ?? null;

            if ($key === null || ! $cloud->exists($key)) {
                $keep['no_cloud_copy']++;

                continue;
            }

            $deletable[] = $path;
            $bytes += filesize($path);
        }

        $this->table(['Локальные файлы', 'Количество'], [
            ['оставить: нужны записям на локальном диске', $keep['needed']],
            ['оставить: копии в облаке нет (не трогаем)', $keep['no_cloud_copy']],
            ['можно удалить: копия в облаке подтверждена', count($deletable)],
        ]);
        $this->line('Освободится: ' . round($bytes / 1048576, 1) . ' МБ');

        if (! $this->option('apply')) {
            $this->comment('Пробный прогон. Для удаления добавьте --apply.');

            return self::SUCCESS;
        }

        foreach ($deletable as $path) {
            unlink($path);
        }

        $this->info('Удалено файлов: ' . count($deletable));

        return self::SUCCESS;
    }
}
