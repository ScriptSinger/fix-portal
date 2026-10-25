<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Concerns\ResolvesPilotView;
use App\Http\Controllers\Controller;
use App\Models\Firmware;
use App\Services\FirmwareCatalog;

/**
 * Страницы-хабы каталога прошивок: марка (/firmwares/indesit) и серия (/firmwares/indesit/wisl).
 */
class FirmwareHubController extends Controller
{
    use ResolvesPilotView;

    public function __construct(private readonly FirmwareCatalog $catalog)
    {
    }

    public function brand(string $brand)
    {
        $name = FirmwareCatalog::brandName($brand);
        $info = $name ? $this->catalog->brands()->get($brand) : null;

        abort_unless($info, 404);

        $series = $this->catalog->seriesList($name);
        $summary = $this->catalog->brandSummary($name);

        return view($this->pilotView('firmwares.hub_brand'), [
            'brand' => $info,
            'series' => $series,
            'summary' => $summary,
            'otherBrands' => $this->catalog->brands()->except($brand),
            'description' => $this->brandDescription($info, $series->count(), $summary),
        ]);
    }

    public function series(string $brand, string $series)
    {
        $name = FirmwareCatalog::brandName($brand);
        $info = $name ? $this->catalog->brands()->get($brand) : null;
        $seriesName = strtoupper($series);
        $summary = $name ? $this->catalog->seriesSummary($name, $seriesName) : null;

        abort_unless($info && $summary, 404);

        $firmwares = Firmware::query()
            ->where('brand', $name)
            ->where('series', $seriesName)
            ->orderBy('model')
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        return view($this->pilotView('firmwares.hub_series'), [
            'brand' => $info,
            'seriesName' => $seriesName,
            'summary' => $summary,
            'firmwares' => $firmwares,
            'description' => $this->seriesDescription($info['name'], $seriesName, $summary),
        ]);
    }

    /**
     * @param  array{name: string, pages: int}  $info
     * @param  array{platforms: array<string, int>}  $summary
     */
    private function brandDescription(array $info, int $seriesCount, array $summary): string
    {
        $platforms = collect($summary['platforms'])->keys()->take(3)->implode(', ');

        $text = "Прошивки {$info['name']}: {$info['pages']} файлов, {$seriesCount} серий моделей"
            . ($platforms !== '' ? ", платформы {$platforms}" : '')
            . '. Скачать дампы модулей управления на UfaMasters.';

        return mb_strlen($text) <= 160 ? $text : rtrim(mb_substr($text, 0, 157), ' ,;:') . '…';
    }

    /**
     * @param  array{pages: int, models: array<string, int>, platforms: array<string, int>}  $summary
     */
    private function seriesDescription(string $brand, string $series, array $summary): string
    {
        $models = collect($summary['models'])->keys()->take(3)->implode(', ');
        $platforms = collect($summary['platforms'])->keys()->take(2)->implode(', ');

        $text = "Прошивки {$brand} {$series}: {$summary['pages']} файлов"
            . ($models !== '' ? ", модели {$models}" : '')
            . ($platforms !== '' ? ", платформа {$platforms}" : '')
            . '. Скачать на UfaMasters.';

        return mb_strlen($text) <= 160 ? $text : rtrim(mb_substr($text, 0, 157), ' ,;:') . '…';
    }
}
