<?php

namespace App\Console\Commands;

use App\Models\Firmware;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MigrateFirmwareToCloud extends Command
{
    protected $signature = 'firmware:migrate-storage
        {--apply : Загрузить файлы и обновить записи (без флага только отчёт)}
        {--limit=0 : Обработать не больше N записей (для постепенного переноса)}
        {--disk=firmware_s3 : Диск назначения}';

    protected $description = 'Переносит файлы прошивок в облачное хранилище. Ключ объекта = MD5 содержимого, поэтому дубли хранятся один раз';

    public function handle(): int
    {
        $diskName = (string) $this->option('disk');
        $target = Storage::disk($diskName);
        $local = Storage::disk('public');
        $apply = (bool) $this->option('apply');
        $limit = max(0, (int) $this->option('limit'));

        $query = Firmware::query()
            ->whereNull('file_disk')
            ->whereNotNull('file_path')
            ->orderBy('id');

        $total = (clone $query)->count();
        $this->info("Записей на локальном диске: {$total}" . ($limit ? ", беру первые {$limit}" : ''));

        $stats = ['linked' => 0, 'uploaded' => 0, 'reused' => 0, 'missing_local' => 0, 'hash_mismatch' => 0, 'failed' => 0];
        $uploadedBytes = 0;
        $seen = [];     // md5 => ключ: файл с таким содержимым уже обработан в этом запуске
        $processed = 0;

        $query->chunkById(200, function ($chunk) use (&$stats, &$uploadedBytes, &$seen, &$processed, $limit, $apply, $target, $local, $diskName) {
            foreach ($chunk as $firmware) {
                if ($limit && $processed >= $limit) {
                    return false;
                }
                $processed++;

                $relative = 'firmwares/' . $firmware->file_path;

                if (! $local->exists($relative)) {
                    $stats['missing_local']++;

                    continue;
                }

                $absolute = $local->path($relative);
                $md5 = md5_file($absolute);
                $recorded = strtolower(trim((string) $firmware->crc32));

                // Хеш в записи есть и не совпал с содержимым: файл подменён, переносить такую связь нельзя
                if (preg_match('/^[a-f0-9]{32}$/', $recorded) === 1 && $recorded !== $md5) {
                    $stats['hash_mismatch']++;

                    continue;
                }

                $key = 'firmwares/' . substr($md5, 0, 2) . '/' . $md5;

                try {
                    if (! isset($seen[$md5])) {
                        if ($apply) {
                            if ($target->exists($key)) {
                                $stats['reused']++;
                            } else {
                                $stream = fopen($absolute, 'rb');
                                $target->writeStream($key, $stream);
                                is_resource($stream) && fclose($stream);

                                // Проверяем, что в облаке лежит ровно то, что отправили
                                if ($target->size($key) !== filesize($absolute) || $this->etag($target, $key) !== $md5) {
                                    $target->delete($key);
                                    throw new \RuntimeException('размер или ETag не совпали после загрузки');
                                }

                                $stats['uploaded']++;
                                $uploadedBytes += filesize($absolute);
                            }
                        } else {
                            $stats['uploaded']++;
                            $uploadedBytes += filesize($absolute);
                        }

                        $seen[$md5] = $key;
                    }

                    if ($apply) {
                        DB::table('firmware')->where('id', $firmware->id)->update(['file_disk' => $diskName, 'file_path' => $key]);
                    }

                    $stats['linked']++;
                } catch (Throwable $e) {
                    $stats['failed']++;
                    $this->error("#{$firmware->id} {$firmware->title}: " . $e->getMessage());
                }
            }

            return true;
        });

        $this->table(['Итог', 'Количество'], [
            [$apply ? 'записей перенесено на диск ' . $diskName : 'записей будет перенесено', $stats['linked']],
            [$apply ? 'файлов загружено' : 'файлов будет загружено (уникальных)', $stats['uploaded']],
            ['файлов уже было в облаке', $stats['reused']],
            ['локального файла нет', $stats['missing_local']],
            ['хеш не совпал с содержимым', $stats['hash_mismatch']],
            ['ошибок', $stats['failed']],
        ]);
        $this->line('Объём загрузки: ' . round($uploadedBytes / 1048576, 1) . ' МБ');

        if (! $apply) {
            $this->comment('Пробный прогон. Для загрузки добавьте --apply (при первом запуске лучше с --limit=50).');
        }

        return $stats['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * ETag одиночной загрузки равен MD5 содержимого.
     */
    private function etag($disk, string $key): string
    {
        $head = $disk->getClient()->headObject([
            'Bucket' => config('filesystems.disks.' . $this->option('disk') . '.bucket'),
            'Key' => $key,
        ]);

        return trim((string) $head['ETag'], '"');
    }
}
