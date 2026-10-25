<?php

namespace App\Console\Commands;

use App\Support\UserImages;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MakeUserImageThumbnails extends Command
{
    protected $signature = 'images:thumbnails {--dry-run : только показать, сколько копий будет создано}';

    protected $description = 'Создаёт уменьшенные копии (480 и 1024 px) для фото пользователей, загруженных до появления копий';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        // оригиналы лежат в images/users/user_N/файл; готовые копии (в папках wNNN) пропускаем
        $files = collect($disk->allFiles('images/users'))
            ->reject(fn (string $path): bool => (bool) preg_match('#/w\d+/#', $path));

        $made = 0;
        $before = 0;
        $after = 0;
        foreach ($files as $path) {
            $before += $disk->size($path);
            if ($this->option('dry-run')) {
                continue;
            }
            try {
                $made += UserImages::makeVariants($path);
            } catch (\Throwable $e) {
                $this->warn("Пропущен {$path}: {$e->getMessage()}");
            }
        }

        foreach ($files as $path) {
            $variant = UserImages::variantPath($path, 480);
            $after += $disk->exists($variant) ? $disk->size($variant) : $disk->size($path);
        }

        $this->info(sprintf('Файлов: %d, создано копий: %d. Размер для карточек: %s МБ вместо %s МБ.',
            $files->count(), $made, number_format($after / 1048576, 2), number_format($before / 1048576, 2)));

        return self::SUCCESS;
    }
}
