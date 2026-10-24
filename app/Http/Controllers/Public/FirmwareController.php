<?php

namespace App\Http\Controllers\Public;

use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\Firmware;

class FirmwareController extends Controller
{
    public function index()
    {
        $query = Firmware::query();

        if (request()->filled('platform')) {
            $query->where('platform', request('platform'));
        }
        if (request()->filled('extension')) {
            $query->where('extension', request('extension'));
        }
        if (request()->filled('crc32')) {
            $query->where('crc32', request('crc32'));
        }
        if (request()->filled('search')) {
            $search = trim(request('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%');
                $this->applyStructuredDataSearch($q, $search);

                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search);
                }
            });
        }

        $sort = request('sort', 'id');
        $dir = request('dir', 'desc');
        if (!in_array($sort, ['id', 'title', 'size', 'date', 'extension', 'platform', 'views_count', 'downloads_count'])) {
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

        $platforms = Firmware::query()
            ->whereNotNull('platform')
            ->where('platform', '!=', '')
            ->distinct()
            ->orderBy('platform')
            ->pluck('platform');

        $extensions = Firmware::query()
            ->whereNotNull('extension')
            ->where('extension', '!=', '')
            ->distinct()
            ->orderBy('extension')
            ->pluck('extension');

        return view('public.firmwares.index_server', compact('firmwares', 'platforms', 'extensions', 'sort', 'dir', 'perPage'));
    }

    public function show(string $slug)
    {
        $firmware = Firmware::findOrFail($slug);
        $firmware->increment('views_count');
        $firmware->refresh();

        return view('public.firmwares.show', compact('firmware'));
    }

    public function download(int $id)
    {
        $firmware = Firmware::findOrFail($id);
        $disk = Storage::disk($firmware->disk_name);
        $key = $firmware->storage_path;

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
