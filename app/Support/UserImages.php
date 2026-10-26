<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

/**
 * Фото пользователей (вопросы, комментарии): оригинал хранится как есть, рядом лежат уменьшенные копии.
 * images/users/user_5/abc.jpg  ->  images/users/user_5/w480/abc.jpg  и  .../w1024/abc.jpg
 * Карточка в списке берёт 480 px, текст вопроса 1024 px, окно просмотра оригинал.
 */
class UserImages
{
    public const WIDTHS = [480, 1024];

    private const DISK = 'public';

    /** Сохраняет оригинал и копии, возвращает путь оригинала на диске. */
    public static function store(UploadedFile $file, string $folder): string
    {
        $path = $file->store($folder, self::DISK);
        self::makeVariants($path);

        return $path;
    }

    /** Создаёт копии для файла на диске; уже существующие пропускает. Возвращает число созданных. */
    public static function makeVariants(string $path): int
    {
        $disk = Storage::disk(self::DISK);
        if (! $disk->exists($path) || ! in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            return 0;
        }

        $made = 0;
        foreach (self::WIDTHS as $width) {
            $target = self::variantPath($path, $width);
            if ($disk->exists($target)) {
                continue;
            }
            // read() сам поворачивает фото по метке ориентации камеры; scaleDown не увеличивает маленькие снимки
            $image = Image::read($disk->path($path))->scaleDown(width: $width);
            $disk->makeDirectory(dirname($target));
            $image->toJpeg(78)->save($disk->path($target));
            $made++;
        }

        return $made;
    }

    public static function variantPath(string $path, int $width): string
    {
        $info = pathinfo($path);

        return $info['dirname'] . '/w' . $width . '/' . $info['filename'] . '.jpg';
    }

    /** Адрес копии нужной ширины для публичного адреса оригинала; если копии нет, возвращает сам оригинал. */
    public static function url(?string $url, int $width): ?string
    {
        if (! $url) {
            return $url;
        }
        $path = parse_url($url, PHP_URL_PATH);
        if (! $path || ! preg_match('#^/storage/(images/users/.+\.(?:jpe?g|png|gif|webp))$#i', $path, $m)) {
            return $url;
        }
        $variant = self::variantPath($m[1], $width);

        return Storage::disk(self::DISK)->exists($variant) ? Storage::url($variant) : $url;
    }
}
