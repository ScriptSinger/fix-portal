<?php

namespace App\Services\Firmware;

use App\Models\Firmware;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/** Отдача файла прошивки: локальный файл отдаём сами, из облака перенаправляем на временную подписанную ссылку. */
class Downloader
{
    /** @throws FileUnavailable если файла нет */
    public function respond(Firmware $firmware): RedirectResponse|BinaryFileResponse
    {
        $disk = Storage::disk($firmware->disk_name);
        $key = $firmware->storage_path;

        if (! $disk->exists($key)) {
            throw new FileUnavailable($key);
        }

        $firmware->increment('downloads_count');

        // Трафик из облака идёт мимо сервера, а права проверены выше (маршрут под auth и verified)
        if ($firmware->file_disk) {
            return redirect()->away($disk->temporaryUrl($key, now()->addMinutes(10), [
                'ResponseContentDisposition' => "attachment; filename*=UTF-8''" . rawurlencode($firmware->download_name),
            ]));
        }

        return response()->download($disk->path($key), $firmware->download_name);
    }
}
