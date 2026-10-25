<?php

namespace App\Services;

use App\Models\Firmware;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Каталог прошивок по маркам и сериям: данные для страниц-хабов
 * (/firmwares/indesit, /firmwares/indesit/wisl). Считается по полям, которые заполняет firmware:enrich.
 */
class FirmwareCatalog
{
    /** Серии меньше этого размера отдельной страницы не получают: это тонкий контент. */
    public const MIN_SERIES_PAGES = 3;

    private const CACHE_SECONDS = 600;

    /**
     * Марки из правил config/firmware_brands.php: slug => название. Не требует базы,
     * поэтому годится для ограничений маршрутов.
     *
     * @return array<string, string>
     */
    public static function brandSlugs(): array
    {
        $names = array_merge(
            array_values((array) config('firmware_brands.families')),
            collect((array) config('firmware_brands.ambiguous_families'))->flatten()->all()
        );

        return collect($names)->unique()->mapWithKeys(fn (string $name) => [static::slug($name) => $name])->all();
    }

    public static function slug(string $name): string
    {
        return Str::slug($name);
    }

    public static function brandName(string $slug): ?string
    {
        return static::brandSlugs()[$slug] ?? null;
    }

    /**
     * Марки, у которых есть прошивки: slug => [name, pages, series].
     *
     * @return Collection<string, array{name: string, slug: string, pages: int, series: int}>
     */
    public function brands(): Collection
    {
        return Cache::remember('catalog.brands', self::CACHE_SECONDS, function () {
            $rows = Firmware::query()
                ->whereNotNull('brand')
                ->select('brand', DB::raw('count(*) as pages'), DB::raw('count(distinct series) as series'))
                ->groupBy('brand')
                ->orderByDesc('pages')
                ->get();

            return $rows->mapWithKeys(fn ($row) => [
                static::slug($row->brand) => [
                    'name' => $row->brand,
                    'slug' => static::slug($row->brand),
                    'pages' => (int) $row->pages,
                    'series' => (int) $row->series,
                ],
            ]);
        });
    }

    /**
     * Серии марки с достаточным числом прошивок.
     *
     * @return Collection<int, array{series: string, slug: string, pages: int, models: int, platforms: list<string>}>
     */
    public function seriesList(string $brand): Collection
    {
        return Cache::remember('catalog.series.' . md5($brand), self::CACHE_SECONDS, function () use ($brand) {
            return Firmware::query()
                ->where('brand', $brand)
                ->whereNotNull('series')
                ->select(
                    'series',
                    DB::raw('count(*) as pages'),
                    DB::raw('count(distinct model) as models'),
                    DB::raw("group_concat(distinct platform_norm order by platform_norm separator ',') as platforms")
                )
                ->groupBy('series')
                ->having('pages', '>=', self::MIN_SERIES_PAGES)
                ->orderByDesc('pages')
                ->get()
                ->map(fn ($row) => [
                    'series' => $row->series,
                    'slug' => strtolower($row->series),
                    'pages' => (int) $row->pages,
                    'models' => (int) $row->models,
                    'platforms' => array_values(array_filter(explode(',', (string) $row->platforms))),
                ]);
        });
    }

    /**
     * Сводка по марке: платформы и форматы.
     *
     * @return array{pages: int, platforms: array<string, int>, extensions: array<string, int>, group: ?string}
     */
    public function brandSummary(string $brand): array
    {
        return Cache::remember('catalog.brand_summary.' . md5($brand), self::CACHE_SECONDS, function () use ($brand) {
            return [
                'pages' => Firmware::query()->where('brand', $brand)->count(),
                'platforms' => $this->distribution('brand', $brand, 'platform_norm'),
                'extensions' => $this->distribution('brand', $brand, 'extension'),
                'group' => Firmware::query()->where('brand', $brand)->whereNotNull('brand_group')->value('brand_group'),
            ];
        });
    }

    /**
     * Сводка по серии или null, если такой серии (или достаточно крупной) нет.
     *
     * @return array{pages: int, models: array<string, int>, platforms: array<string, int>, extensions: array<string, int>, year_from: ?int, year_to: ?int}|null
     */
    public function seriesSummary(string $brand, string $series): ?array
    {
        return Cache::remember('catalog.series_summary.' . md5($brand . '|' . $series), self::CACHE_SECONDS, function () use ($brand, $series) {
            $base = Firmware::query()->where('brand', $brand)->where('series', $series);
            $pages = (clone $base)->count();

            if ($pages < self::MIN_SERIES_PAGES) {
                return null;
            }

            $years = (clone $base)->selectRaw('min(year(date)) as y1, max(year(date)) as y2')->first();

            return [
                'pages' => $pages,
                'models' => (clone $base)->whereNotNull('model')
                    ->select('model', DB::raw('count(*) as c'))->groupBy('model')->orderByDesc('c')->orderBy('model')->limit(60)->pluck('c', 'model')->map(fn ($c) => (int) $c)->all(),
                'platforms' => $this->distribution('series', $series, 'platform_norm', $brand),
                'extensions' => $this->distribution('series', $series, 'extension', $brand),
                'year_from' => $years?->y1 ? (int) $years->y1 : null,
                'year_to' => $years?->y2 ? (int) $years->y2 : null,
            ];
        });
    }

    /**
     * @return array<string, int>
     */
    private function distribution(string $column, string $value, string $by, ?string $brand = null): array
    {
        return Firmware::query()
            ->when($brand, fn ($q) => $q->where('brand', $brand))
            ->where($column, $value)
            ->whereNotNull($by)
            ->where($by, '!=', '')
            ->select($by, DB::raw('count(*) as c'))
            ->groupBy($by)
            ->orderByDesc('c')
            ->pluck('c', $by)
            ->map(fn ($c) => (int) $c)
            ->all();
    }

    /**
     * Есть ли у серии своя страница.
     */
    public function hasSeriesHub(?string $brand, ?string $series): bool
    {
        return $brand && $series
            && $this->seriesList($brand)->contains(fn (array $row) => $row['series'] === $series);
    }
}
