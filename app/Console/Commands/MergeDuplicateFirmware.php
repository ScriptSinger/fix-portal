<?php

namespace App\Console\Commands;

use App\Models\Firmware;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MergeDuplicateFirmware extends Command
{
    protected $signature = 'firmware:merge-duplicates
        {--apply : Выполнить слияние (без флага только отчёт)}
        {--sample=5 : Сколько групп показать в примере}';

    protected $description = 'Сливает записи с одинаковым содержимым (MD5) в одну: переносит просмотры, скачивания и комментарии, названия сохраняет как псевдонимы, дубли удаляет, а их адреса записывает в firmware_redirects (отвечают 301)';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $hashes = Firmware::query()
            ->whereNotNull('crc32')
            ->whereRaw("crc32 regexp '^[a-f0-9]{32}$'")
            ->selectRaw('crc32')
            ->groupBy('crc32')
            ->havingRaw('count(*) > 1')
            ->pluck('crc32');

        $stats = ['groups' => 0, 'removed' => 0, 'views' => 0, 'downloads' => 0, 'comments' => 0, 'aliases' => 0];
        $examples = [];

        foreach ($hashes as $hash) {
            $records = Firmware::query()
                ->where('crc32', $hash)
                ->orderBy('id')
                ->get(['id', 'title', 'extension', 'aliases', 'views_count', 'downloads_count']);

            $keep = $records->first();
            $duplicates = $records->slice(1);

            // Другие названия файла: без повторов и без названия основной записи
            $names = $duplicates
                ->map(fn ($d) => trim($d->title . ($d->extension ?? '')))
                ->filter(fn ($name) => $name !== '' && $name !== trim($keep->title . ($keep->extension ?? '')))
                ->merge(array_filter(explode("\n", (string) $keep->aliases)))
                ->unique()
                ->values()
                ->take(20);

            $views = (int) $duplicates->sum('views_count');
            $downloads = (int) $duplicates->sum('downloads_count');
            $comments = DB::table('comments')
                ->where('commentable_type', Firmware::class)
                ->whereIn('commentable_id', $duplicates->pluck('id'))
                ->count();

            $stats['groups']++;
            $stats['removed'] += $duplicates->count();
            $stats['views'] += $views;
            $stats['downloads'] += $downloads;
            $stats['comments'] += $comments;
            $stats['aliases'] += $names->count();

            if (count($examples) < (int) $this->option('sample')) {
                $examples[] = [
                    '#' . $keep->id,
                    $duplicates->pluck('id')->map(fn ($id) => '#' . $id)->implode(', '),
                    $names->take(3)->implode(' | ') ?: '-',
                    $views . ' / ' . $downloads,
                ];
            }

            if (! $apply) {
                continue;
            }

            DB::transaction(function () use ($keep, $duplicates, $names, $views, $downloads) {
                DB::table('comments')
                    ->where('commentable_type', Firmware::class)
                    ->whereIn('commentable_id', $duplicates->pluck('id'))
                    ->update(['commentable_id' => $keep->id]);

                DB::table('firmware')->where('id', $keep->id)->update([
                    'views_count' => DB::raw('views_count + ' . $views),
                    'downloads_count' => DB::raw('downloads_count + ' . $downloads),
                    'aliases' => $names->isEmpty() ? null : $names->implode("\n"),
                ]);

                // Адреса, которые вели на сливаемые записи, теперь ведут на основную; потом дубли стираются
                DB::table('firmware_redirects')->whereIn('new_id', $duplicates->pluck('id'))->update(['new_id' => $keep->id]);
                foreach ($duplicates as $duplicate) {
                    DB::table('firmware_redirects')->updateOrInsert(['old_id' => $duplicate->id], ['new_id' => $keep->id]);
                }
                DB::table('firmware')->whereIn('id', $duplicates->pluck('id'))->delete();
            });
        }

        $this->table(['Показатель', 'Значение'], [
            ['групп дублей', $stats['groups']],
            [$apply ? 'записей слито и удалено' : 'записей будет слито', $stats['removed']],
            ['просмотров переносится', $stats['views']],
            ['скачиваний переносится', $stats['downloads']],
            ['комментариев переносится', $stats['comments']],
            ['псевдонимов (других названий файла)', $stats['aliases']],
        ]);

        $this->line('Примеры (основная запись | дубли | другие названия | просмотры / скачивания дублей):');
        $this->table(['Основная', 'Дубли', 'Другие названия', 'Просм./скач.'], $examples);

        if (! $apply) {
            $this->comment('Пробный прогон. Для слияния добавьте --apply (дубли будут удалены, их адреса останутся редиректами).');
        }

        return self::SUCCESS;
    }
}
