<?php

namespace App\Console\Commands;

use App\Models\Firmware;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RelinkFirmwareFiles extends Command
{
    protected $signature = 'firmware:relink
        {--apply : Записать file_path (без флага только отчёт)}
        {--delete-missing : Вместе с --apply: мягко удалить записи, содержимое которых нет на диске}';

    protected $description = 'Привязывает записи прошивок к файлам по MD5 содержимого и находит записи без файла';

    public function handle(): int
    {
        $dir = Storage::disk('public')->path('firmwares');

        if (! is_dir($dir)) {
            $this->error("Каталог не найден: {$dir}");

            return self::FAILURE;
        }

        $this->info('Считаю MD5 файлов в ' . $dir . ' ...');
        $filesByHash = [];
        $names = [];

        foreach (scandir($dir) as $name) {
            if (! is_file("{$dir}/{$name}")) {
                continue;
            }

            $names[$name] = true;
            $filesByHash[md5_file("{$dir}/{$name}")][] = $name;
        }

        foreach ($filesByHash as &$list) {
            sort($list);
        }
        unset($list);

        $this->line('Файлов: ' . count($names) . ', уникальных содержимых: ' . count($filesByHash));

        $stats = ['same' => 0, 'relinked' => 0, 'unverified' => 0, 'lost' => 0];
        $updates = [];
        $lost = [];

        Firmware::query()->select(['id', 'title', 'extension', 'crc32', 'file_path', 'downloads_count'])
            ->orderBy('id')
            ->chunkById(1000, function ($chunk) use (&$stats, &$updates, &$lost, $filesByHash, $names): void {
                foreach ($chunk as $firmware) {
                    $expected = $firmware->title . ($firmware->extension ?? '');
                    $hash = strtolower(trim((string) $firmware->crc32));

                    if (preg_match('/^[a-f0-9]{32}$/', $hash) === 1) {
                        $candidates = $filesByHash[$hash] ?? [];

                        if ($candidates === []) {
                            $stats['lost']++;
                            $lost[] = $firmware;

                            continue;
                        }

                        // Загрузка заменяла пробелы на "_": сначала точное имя, потом такой вариант, потом любой с тем же содержимым
                        $chosen = in_array($expected, $candidates, true)
                            ? $expected
                            : (in_array(str_replace(' ', '_', $expected), $candidates, true)
                                ? str_replace(' ', '_', $expected)
                                : $candidates[0]);

                        $stats[$chosen === $expected ? 'same' : 'relinked']++;
                    } elseif (isset($names[$expected])) {
                        // Хеша нет, проверить содержимое нельзя, но файл с таким именем есть
                        $chosen = $expected;
                        $stats['unverified']++;
                    } else {
                        $stats['lost']++;
                        $lost[] = $firmware;

                        continue;
                    }

                    if ($firmware->file_path !== $chosen) {
                        $updates[$firmware->id] = $chosen;
                    }
                }
            });

        $this->table(['Категория', 'Записей'], [
            ['файл с тем же именем и содержимым', $stats['same']],
            ['файл найден под другим именем (перепривязка)', $stats['relinked']],
            ['без хеша, но файл по имени есть', $stats['unverified']],
            ['содержимого на диске нет', $stats['lost']],
        ]);
        $this->line('К обновлению file_path: ' . count($updates));
        $this->line('Без содержимого, скачивали (downloads_count > 0): ' . collect($lost)->filter(fn ($f) => (int) $f->downloads_count > 0)->count());

        if (! $this->option('apply')) {
            $this->comment('Пробный прогон. Для записи добавьте --apply (и --delete-missing, чтобы удалить записи без содержимого).');

            return self::SUCCESS;
        }

        foreach (array_chunk($updates, 500, true) as $part) {
            DB::transaction(function () use ($part): void {
                foreach ($part as $id => $path) {
                    DB::table('firmware')->where('id', $id)->update(['file_path' => $path]);
                }
            });
        }

        $this->info('file_path записан: ' . count($updates));

        if ($this->option('delete-missing')) {
            $deleted = 0;

            foreach (collect($lost)->chunk(200) as $part) {
                // Через модель: Firmware при удалении убирает и комментарии с ответами
                Firmware::whereIn('id', $part->pluck('id'))->get()->each(function (Firmware $firmware) use (&$deleted): void {
                    $firmware->delete();
                    $deleted++;
                });
            }

            $this->info("Мягко удалено записей без содержимого: {$deleted}");
        }

        return self::SUCCESS;
    }
}
