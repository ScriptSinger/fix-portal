<?php

namespace App\Services\Backup;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Дампы базы: mysqldump со сжатием, проверка целостности, копия в приватный
 * бакет Yandex Object Storage и ротация старых копий на сервере и в облаке.
 *
 * Параметры подключения передаются через переменные окружения процесса,
 * а не аргументами: пароль не попадает в список процессов, а имена файлов
 * и базы не попадают в shell без экранирования.
 */
class DatabaseBackup
{
    private readonly string $connection;

    public function __construct(?string $connection = null)
    {
        $this->connection = $connection ?? config('database.default');
    }

    /**
     * Снять дамп и проверить его. Возвращает путь к файлу.
     */
    public function create(?string $label = null): string
    {
        File::ensureDirectoryExists($this->directory());

        $file = $this->directory() . '/' . $this->fileName($label);

        $result = Process::env([...$this->credentials(), 'BACKUP_FILE' => $file])
            ->timeout(1800)
            ->run([
                'bash', '-c',
                'set -o pipefail; mysqldump --host="$DB_HOST" --port="$DB_PORT" --user="$DB_USER" '
                . '--single-transaction --quick --routines --triggers --no-tablespaces --set-gtid-purged=OFF '
                . '--default-character-set=utf8mb4 "$DB_NAME" | gzip > "$BACKUP_FILE"',
            ]);

        if ($result->failed()) {
            File::delete($file);

            throw new RuntimeException('Дамп не снят: ' . trim($result->errorOutput()));
        }

        if (! $this->isComplete($file)) {
            File::delete($file);

            throw new RuntimeException('Дамп оборвался: в конце файла нет отметки о завершении.');
        }

        return $file;
    }

    /**
     * Архив читается целиком и заканчивается строкой «-- Dump completed»,
     * которую mysqldump пишет только при успешном завершении.
     */
    public function isComplete(string $file): bool
    {
        return Process::env(['BACKUP_FILE' => $file])
            ->run(['bash', '-c', 'set -o pipefail; gzip -t "$BACKUP_FILE" && gzip -dc "$BACKUP_FILE" | tail -n 1 | grep -q "Dump completed"'])
            ->successful();
    }

    /**
     * Залить копию в облако и сверить размер загруженного файла с локальным.
     * Возвращает путь в бакете.
     */
    public function upload(string $file): string
    {
        if (blank(config('filesystems.disks.' . config('backup.cloud_disk') . '.bucket'))) {
            throw new RuntimeException('Не задан бакет для копий (BACKUP_S3_BUCKET в .env).');
        }

        $disk = $this->cloud();
        $path = $this->cloudPath(basename($file));

        $stream = fopen($file, 'rb');

        try {
            $disk->writeStream($path, $stream);
        } catch (\Throwable $exception) {
            throw new RuntimeException('Копия не загружена в облако: ' . $exception->getMessage());
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        if ($disk->size($path) !== File::size($file)) {
            throw new RuntimeException('Копия в облаке не совпадает по размеру с локальной.');
        }

        return $path;
    }

    /**
     * Удалить копии на сервере старше срока хранения. Самую свежую не трогаем никогда.
     *
     * @return list<string> удалённые файлы
     */
    public function prune(int $keepDays): array
    {
        $threshold = now()->subDays($keepDays);

        return $this->all()
            ->skip(1)
            ->filter(fn (array $backup): bool => $backup['created_at']->lt($threshold))
            ->each(fn (array $backup): bool => File::delete($backup['path']))
            ->pluck('path')
            ->values()
            ->all();
    }

    /**
     * То же для облака: старые копии удаляются, самая свежая остаётся всегда.
     *
     * @return list<string> удалённые пути в бакете
     */
    public function pruneCloud(int $keepDays): array
    {
        $disk = $this->cloud();
        $threshold = now()->subDays($keepDays)->getTimestamp();

        return collect($disk->files(config('backup.cloud_prefix')))
            ->filter(fn (string $path): bool => str_ends_with($path, '.sql.gz'))
            ->mapWithKeys(fn (string $path): array => [$path => $disk->lastModified($path)])
            ->sortDesc()
            ->skip(1)
            ->filter(fn (int $modified): bool => $modified < $threshold)
            ->keys()
            ->each(fn (string $path): bool => $disk->delete($path))
            ->values()
            ->all();
    }

    /**
     * Копии на сервере от новых к старым.
     *
     * @return Collection<int, array{path: string, name: string, size: int, created_at: Carbon}>
     */
    public function all(): Collection
    {
        if (! File::isDirectory($this->directory())) {
            return collect();
        }

        return collect(File::glob($this->directory() . '/*.sql.gz'))
            ->map(fn (string $path): array => [
                'path' => $path,
                'name' => basename($path),
                'size' => File::size($path),
                'created_at' => Carbon::createFromTimestamp(File::lastModified($path)),
            ])
            ->sortByDesc('created_at')
            ->values();
    }

    public function directory(): string
    {
        return rtrim((string) config('backup.path'), '/');
    }

    private function cloud(): Filesystem
    {
        return Storage::disk(config('backup.cloud_disk'));
    }

    private function cloudPath(string $name): string
    {
        return trim((string) config('backup.cloud_prefix'), '/') . '/' . $name;
    }

    private function fileName(?string $label): string
    {
        $suffix = filled($label) ? '-' . Str::slug($label) : '';

        return config("database.connections.{$this->connection}.database") . '-' . now()->format('Y-m-d_His') . $suffix . '.sql.gz';
    }

    /**
     * @return array<string, string>
     */
    private function credentials(): array
    {
        $config = config("database.connections.{$this->connection}");

        if (($config['driver'] ?? null) !== 'mysql') {
            throw new RuntimeException('Резервное копирование настроено только для MySQL.');
        }

        return [
            'DB_HOST' => (string) $config['host'],
            'DB_PORT' => (string) $config['port'],
            'DB_USER' => (string) $config['username'],
            'DB_NAME' => (string) $config['database'],
            'MYSQL_PWD' => (string) $config['password'],
        ];
    }
}
