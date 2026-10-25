<?php

namespace App\Http\Controllers\Public;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\Firmware;

class FirmwareController extends Controller
{
    public function index()
    {
        $query = Firmware::query();

        if (request()->filled('brand')) {
            $query->where('brand', request('brand'));
        }
        if (request()->filled('series')) {
            $query->where('series', mb_strtoupper(trim((string) request('series'))));
        }
        if (request()->filled('platform')) {
            // platform_norm склеивает Arcadia / ARCADIA / arcadia; старое поле оставляем для записей без обогащения
            $platform = request('platform');
            $query->where(function ($q) use ($platform) {
                $q->where('platform_norm', $platform)->orWhere('platform', $platform);
            });
        }
        if (request()->filled('extension')) {
            $query->where('extension', request('extension'));
        }
        if (request()->filled('crc32')) {
            $query->where('crc32', request('crc32'));
        }
        if (request()->filled('search')) {
            $this->applySearch($query, trim(request('search')));
        }

        $sort = request('sort', 'id');
        $dir = request('dir', 'desc');
        if (!in_array($sort, ['id', 'title', 'size', 'date', 'extension', 'platform', 'brand', 'model', 'views_count', 'downloads_count'])) {
            $sort = 'id';
        }
        if (!in_array($dir, ['asc', 'desc'])) {
            $dir = 'desc';
        }

        $perPage = (int) request('per_page', 50);
        if (!in_array($perPage, [25, 50, 100, 200])) {
            $perPage = 50;
        }

        $firmwares = $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();

        // Списки для фильтров почти не меняются: кэшируем на 10 минут
        $filters = Cache::remember('firmware.filters', 600, function () {
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
        $platforms = $filters['platforms'];
        $brands = $filters['brands'];
        $seriesByBrand = $filters['seriesByBrand'];

        $extensions = Firmware::query()
            ->whereNotNull('extension')
            ->where('extension', '!=', '')
            ->distinct()
            ->orderBy('extension')
            ->pluck('extension');

        return view('public.firmwares.index_server', array_merge(compact('firmwares', 'platforms', 'brands', 'seriesByBrand', 'extensions', 'sort', 'dir', 'perPage'), ['catalogBrands' => app(\App\Services\FirmwareCatalog::class)->brands()]));
    }

    public function show(string $slug)
    {
        $firmware = Firmware::withTrashed()->findOrFail($slug);

        // Дубль слит в другую запись: постоянный редирект, чтобы старые ссылки и поисковики вели на основную страницу
        if ($firmware->trashed()) {
            abort_unless($firmware->merged_into, 404);

            return redirect()->route('firmwares.show', ['firmware' => $firmware->merged_into], 301);
        }

        $firmware->increment('views_count');
        $firmware->refresh();

        return view('public.firmwares.show', compact('firmware'));
    }

    public function download(int $id)
    {
        $firmware = Firmware::withTrashed()->findOrFail($id);

        if ($firmware->trashed()) {
            abort_unless($firmware->merged_into, 404);

            return redirect()->route('firmwares.download', ['firmware' => $firmware->merged_into], 301);
        }

        $disk = Storage::disk($firmware->disk_name);
        $key = $firmware->storage_path;

        try {
            if (! $disk->exists($key)) {
                return redirect()->back()->with('error', 'Файл не найден');
            }

            $firmware->increment('downloads_count');

            // Локальный файл отдаём сами, из облака перенаправляем на временную подписанную ссылку:
            // трафик идёт мимо сервера, а права мы уже проверили выше (маршрут под auth и verified)
            if ($firmware->file_disk) {
                return redirect()->away($disk->temporaryUrl($key, now()->addMinutes(10), [
                    'ResponseContentDisposition' => "attachment; filename*=UTF-8''" . rawurlencode($firmware->download_name),
                ]));
            }

            return response()->download($disk->path($key), $firmware->download_name);
        } catch (\Throwable $e) {
            // Хранилище недоступно: показываем понятное сообщение вместо 500 и пишем причину в лог
            report($e);

            return redirect()->back()->with('error', 'Хранилище файлов временно недоступно, попробуйте через несколько минут.');
        }
    }

    /**
     * Поиск идёт в два шага. Сначала по коротким полям (название, модель, серия, алиасы): это миллисекунды.
     * Разбор HTML-описания (поле data, 12 LIKE по каждой записи) занимает около секунды, поэтому
     * подключаем его только если короткие поля ничего не нашли. Наличие совпадений кэшируем,
     * чтобы повторяющиеся запросы (в основном от краулеров) не ходили в базу заново.
     */
    private function applySearch($query, string $search): void
    {
        $fast = function ($q) use ($search) {
            // «wisl 105», «WISL-105» и «WISL105» должны находить одну и ту же модель
            $compact = mb_strtoupper(preg_replace('/[\s\-_]+/u', '', $search) ?? $search);

            $q->where('title', 'like', '%' . $search . '%')
                ->orWhere('model', 'like', '%' . $this->escapeLike(mb_strtoupper($search)) . '%')
                ->orWhere('model', 'like', '%' . $this->escapeLike($compact) . '%')
                ->orWhere('series', $compact)
                ->orWhere('aliases', 'like', '%' . $this->escapeLike($search) . '%');

            if (ctype_digit($search)) {
                $q->orWhere('id', (int) $search);
            }
        };

        $found = Cache::remember(
            'firmware.search.' . md5(mb_strtolower($search)),
            600,
            fn () => Firmware::query()->where($fast)->exists()
        );

        if ($found) {
            $query->where($fast);

            return;
        }

        $query->where(function ($q) use ($fast, $search) {
            $q->where($fast);
            $this->applyStructuredDataSearch($q, $search);
        });
    }

    private function applyStructuredDataSearch($query, string $search): void
    {
        $escaped = $this->escapeLike($search);
        $labels = ['Модель', 'Model', 'S/N', 'SN', 'Серийный номер', 'PNS', 'PNC', 'P/N', 'Part Number', 'Код прошивки', 'Firmware Code', 'Для S/N'];

        foreach ($labels as $label) {
            $query->orWhereRaw(
                "data LIKE ? ESCAPE '\\\\'",
                ['%<td>' . $label . '</td>%<td>' . $escaped . '%</td>%']
            );
        }
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
