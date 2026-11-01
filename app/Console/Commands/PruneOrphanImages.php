<?php

namespace App\Console\Commands;

use App\Models\Image;
use App\Services\ImageCleanupService;
use Illuminate\Console\Command;

class PruneOrphanImages extends Command
{
    protected $signature = 'images:prune-orphans
        {--days=1 : Считать брошенными изображения старше N дней}
        {--apply : Реально удалить. Без флага только показать кандидатов}';

    protected $description = 'Удаляет загруженные, но не использованные в публикациях изображения пользователей';

    public function handle(ImageCleanupService $cleanup): int
    {
        $days = max(1, (int) $this->option('days'));
        $query = Image::withTrashed()->where('created_at', '<', now()->subDays($days));
        $total = (clone $query)->count();

        $this->info("Старых изображений для проверки: {$total}");

        if ($total === 0) {
            return self::SUCCESS;
        }

        if (! $this->option('apply')) {
            $this->comment('Это пробный прогон. Для удаления добавьте --apply.');

            return self::SUCCESS;
        }

        $deleted = 0;
        (clone $query)->select('id')->chunkById(500, function ($images) use ($cleanup, &$deleted): void {
            foreach ($images as $image) {
                $exists = Image::withTrashed()->whereKey($image->id)->exists();
                $cleanup->cleanup([(int) $image->id]);

                if ($exists && ! Image::withTrashed()->whereKey($image->id)->exists()) {
                    $deleted++;
                }
            }
        });

        $this->info("Удалено брошенных изображений: {$deleted}");

        return self::SUCCESS;
    }
}
