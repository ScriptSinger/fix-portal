<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Firmware;
use App\Models\FirmwareRedirect;
use App\Services\Firmware\CatalogQuery;
use App\Services\Firmware\Downloader;
use App\Services\Firmware\FileUnavailable;
use App\Services\Firmware\FilterOptions;
use App\Services\FirmwareCatalog;
use Illuminate\Http\Request;

class FirmwareController extends Controller
{
    public function index(Request $request, CatalogQuery $catalog, FilterOptions $options, FirmwareCatalog $brandsCatalog)
    {
        [$sort, $dir] = $catalog->order($request);
        $perPage = $catalog->perPage($request);
        $firmwares = $catalog->filtered()->orderBy($sort, $dir)->paginate($perPage)->withQueryString();

        $lists = $options->cached();
        $platforms = $lists['platforms'];
        $brands = $lists['brands'];
        $seriesByBrand = $lists['seriesByBrand'];
        $extensions = $options->extensions();
        $catalogBrands = $brandsCatalog->brands();

        return view('public.firmwares.index', compact('firmwares', 'platforms', 'brands', 'seriesByBrand', 'extensions', 'sort', 'dir', 'perPage', 'catalogBrands'));
    }

    public function show(string $slug)
    {
        $firmware = Firmware::find($slug);

        // Дубль слит в другую запись: постоянный редирект, чтобы старые ссылки и поисковики вели на основную страницу
        if (! $firmware) {
            return redirect()->route('firmwares.show', ['firmware' => $this->mergedInto($slug)], 301);
        }

        $firmware->increment('views_count');
        $firmware->refresh();
        $firmware->load(['comments.user.avatar', 'comments.reactions', 'comments.replies.user.avatar', 'comments.replies.reactions']);

        return view('public.firmwares.show', compact('firmware'));
    }

    public function download(int $id, Downloader $downloader)
    {
        $firmware = Firmware::find($id);

        if (! $firmware) {
            return redirect()->route('firmwares.download', ['firmware' => $this->mergedInto($id)], 301);
        }

        try {
            return $downloader->respond($firmware);
        } catch (FileUnavailable) {
            return redirect()->back()->with('error', 'Файл не найден');
        } catch (\Throwable $e) {
            // Хранилище недоступно: показываем понятное сообщение вместо 500 и пишем причину в лог
            report($e);

            return redirect()->back()->with('error', 'Хранилище файлов временно недоступно, попробуйте через несколько минут.');
        }
    }

    /** Номер основной записи, в которую слит дубль с этим номером; 404, если такого дубля не было. */
    private function mergedInto(int|string $id): int
    {
        $new = FirmwareRedirect::whereKey($id)->value('new_id');
        abort_unless($new, 404);

        return (int) $new;
    }
}
