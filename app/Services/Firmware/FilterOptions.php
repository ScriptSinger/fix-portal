<?php

namespace App\Services\Firmware;

use App\Models\Firmware;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/** Значения для панели фильтров каталога: платформы, марки, семейства моделей, расширения. Списки почти не меняются, кэш 10 минут. */
class FilterOptions
{
    /** @return array{platforms: Collection, brands: Collection, seriesByBrand: array<string, list<string>>} */
    public function cached(): array
    {
        return Cache::remember('firmware.filters', 600, function () {
            $platforms = Firmware::query()->whereNotNull('platform_norm')->distinct()->orderBy('platform_norm')->pluck('platform_norm');

            if ($platforms->isEmpty()) {
                // Каталожные поля ещё не заполнены (firmware:enrich): берём платформу как есть
                $platforms = Firmware::query()->whereNotNull('platform')->where('platform', '!=', '')->distinct()->orderBy('platform')->pluck('platform');
            }

            $brands = Firmware::query()
                ->whereNotNull('brand')
                ->select('brand', DB::raw('count(*) as pages'))
                ->groupBy('brand')
                ->orderByDesc('pages')
                ->pluck('pages', 'brand');

            // Семейства моделей по маркам (только достаточно крупные: мелких сотни и они только мешают в списке)
            $seriesByBrand = Firmware::query()
                ->whereNotNull('brand')
                ->whereNotNull('series')
                ->select('brand', 'series', DB::raw('count(*) as pages'))
                ->groupBy('brand', 'series')
                ->having('pages', '>=', 3)
                ->orderBy('series')
                ->get()
                ->groupBy('brand')
                ->map(fn ($rows) => $rows->pluck('series')->values()->all())
                ->all();

            return compact('platforms', 'brands', 'seriesByBrand');
        });
    }

    public function extensions(): Collection
    {
        return Firmware::query()->whereNotNull('extension')->where('extension', '!=', '')->distinct()->orderBy('extension')->pluck('extension');
    }
}
