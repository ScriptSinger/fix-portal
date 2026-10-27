<?php

namespace App\Services\Firmware;

use App\Http\Filters\Firmwares\BrandFilter;
use App\Http\Filters\Firmwares\Crc32Filter;
use App\Http\Filters\Firmwares\ExtensionFilter;
use App\Http\Filters\Firmwares\PlatformFilter;
use App\Http\Filters\Firmwares\SearchFilter;
use App\Http\Filters\Firmwares\SeriesFilter;
use App\Models\Firmware;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;

/** Выборка каталога прошивок для сайта: цепочка фильтров, сортировка и размер страницы из запроса. */
class CatalogQuery
{
    public const SORTABLE = ['id', 'title', 'size', 'date', 'extension', 'platform', 'brand', 'model', 'views_count', 'downloads_count'];
    public const PER_PAGE = [25, 50, 100, 200];

    /** Фильтры по параметрам запроса; каждый читает свой параметр и ничего не делает, если его нет. */
    public const FILTERS = [BrandFilter::class, SeriesFilter::class, PlatformFilter::class, ExtensionFilter::class, Crc32Filter::class, SearchFilter::class];

    public function __construct(private readonly Pipeline $pipeline)
    {
    }

    public function filtered(): Builder
    {
        return $this->pipeline->send(Firmware::query())->through(self::FILTERS)->thenReturn();
    }

    /** @return array{0: string, 1: string} поле и направление; неизвестные значения заменяются безопасными */
    public function order(Request $request): array
    {
        $sort = in_array($request->input('sort'), self::SORTABLE, true) ? $request->input('sort') : 'id';
        $dir = in_array($request->input('dir'), ['asc', 'desc'], true) ? $request->input('dir') : 'desc';

        return [$sort, $dir];
    }

    public function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 50);

        return in_array($perPage, self::PER_PAGE, true) ? $perPage : 50;
    }
}
