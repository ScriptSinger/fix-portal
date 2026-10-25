<?php

namespace App\Console\Commands;

use App\Models\Firmware;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EnrichFirmwareCatalog extends Command
{
    protected $signature = 'firmware:enrich
        {--apply : Записать поля в БД (без флага только отчёт)}
        {--sample=6 : Сколько примеров показать по каждой марке}';

    protected $description = 'Заполняет brand, brand_group, series, model, platform_norm у прошивок по правилам config/firmware_brands.php. Неопределённое остаётся пустым';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $sampleSize = max(0, (int) $this->option('sample'));
        $rules = (array) config('firmware_brands.families');

        $stats = ['total' => 0, 'brand' => 0, 'group' => 0, 'series' => 0, 'model' => 0, 'platform' => 0, 'conflict' => 0, 'changed' => 0];
        $byBrand = [];
        $samples = [];
        $conflicts = [];

        Firmware::query()
            ->select(['id', 'title', 'extension', 'platform', 'data', 'brand', 'brand_group', 'series', 'model', 'platform_norm'])
            ->orderBy('id')
            ->chunkById(500, function ($chunk) use ($apply, $rules, $sampleSize, &$stats, &$byBrand, &$samples, &$conflicts) {
                $updates = [];

                foreach ($chunk as $firmware) {
                    $stats['total']++;
                    $new = $this->derive($firmware, $rules);

                    $stats['brand'] += $new['brand'] ? 1 : 0;
                    $stats['group'] += $new['brand_group'] ? 1 : 0;
                    $stats['series'] += $new['series'] ? 1 : 0;
                    $stats['model'] += $new['model'] ? 1 : 0;
                    $stats['platform'] += $new['platform_norm'] ? 1 : 0;

                    $label = $new['brand'] ?? ($new['brand_group'] ? '(только группа) ' . $new['brand_group'] : '(не определено)');
                    $byBrand[$label] = ($byBrand[$label] ?? 0) + 1;

                    if (count($samples[$label] ?? []) < $sampleSize && $new['model']) {
                        $samples[$label][] = $new['model'];
                    }

                    // Марка и платформа указывают на разные группы: данные или правило требуют проверки
                    if ($new['conflict']) {
                        $stats['conflict']++;
                        if (count($conflicts) < 8) {
                            $conflicts[] = "#{$firmware->id} {$new['model']} ({$new['brand']} / платформа {$new['platform_norm']})";
                        }
                    }

                    $payload = collect($new)->except('conflict')->all();
                    $current = $firmware->only(array_keys($payload));

                    if ($payload !== $current) {
                        $stats['changed']++;
                        $updates[$firmware->id] = $payload;
                    }
                }

                if ($apply && $updates !== []) {
                    DB::transaction(function () use ($updates) {
                        foreach ($updates as $id => $payload) {
                            DB::table('firmware')->where('id', $id)->update($payload);
                        }
                    });
                }
            });

        $pct = fn (int $n) => $n . ' (' . round($n * 100 / max(1, $stats['total'])) . '%)';
        $this->table(['Поле', 'Заполнено'], [
            ['записей всего', $stats['total']],
            ['brand (марка)', $pct($stats['brand'])],
            ['brand_group (группа)', $pct($stats['group'])],
            ['series (семейство модели)', $pct($stats['series'])],
            ['model', $pct($stats['model'])],
            ['platform_norm', $pct($stats['platform'])],
            ['противоречий марка/платформа', $stats['conflict']],
            [$apply ? 'записей обновлено' : 'записей будет обновлено', $stats['changed']],
        ]);

        arsort($byBrand);
        $this->table(
            ['Марка / группа', 'Страниц', 'Примеры моделей'],
            collect($byBrand)->map(fn ($n, $label) => [$label, $n, implode(', ', $samples[$label] ?? [])])->values()->all()
        );

        if ($conflicts) {
            $this->warn('Противоречия (первые): ' . implode('; ', $conflicts));
        }

        if (! $apply) {
            $this->comment('Пробный прогон. Для записи добавьте --apply.');
        }

        return self::SUCCESS;
    }

    /**
     * @param  array<string, string>  $rules
     * @return array{brand: ?string, brand_group: ?string, series: ?string, model: ?string, platform_norm: ?string, conflict: bool}
     */
    private function derive(Firmware $firmware, array $rules): array
    {
        $model = $this->cleanModel($firmware->model_name);
        $source = mb_strtoupper(trim((string) ($model ?? $firmware->title)));
        $series = preg_match('/^([A-Z]+)/u', $source, $m) === 1 ? substr($m[1], 0, 32) : null;

        $platformKey = mb_strtoupper(trim((string) $firmware->platform));

        $brand = null;
        foreach ($rules as $pattern => $name) {
            if ($series !== null && preg_match($pattern, $series) === 1) {
                $brand = $name;
                break;
            }
        }

        // Одинаковый префикс у разных марок: смотрим на платформу
        if ($brand === null && $series !== null) {
            $brand = config('firmware_brands.ambiguous_families.' . $series . '.' . $platformKey);
        }

        $platformNorm = config('firmware_brands.platforms.' . $platformKey);
        $platformGroup = config('firmware_brands.platform_groups.' . $platformKey);
        $brandGroup = $brand ? config('firmware_brands.brand_groups.' . $brand) : null;

        return [
            'brand' => $brand,
            'brand_group' => $brandGroup ?? $platformGroup,
            'series' => $series,
            'model' => $model,
            'platform_norm' => $platformNorm,
            'conflict' => $brandGroup !== null && $platformGroup !== null && $brandGroup !== $platformGroup,
        ];
    }

    private function cleanModel(?string $value): ?string
    {
        $value = mb_strtoupper(trim(preg_replace('/\s+/u', ' ', (string) $value) ?? ''));

        if ($value === '' || in_array($value, ['Н.Д.', 'Н/Д', 'N/A', '-', '--', '---'], true)) {
            return null;
        }

        return mb_substr($value, 0, 64);
    }
}
