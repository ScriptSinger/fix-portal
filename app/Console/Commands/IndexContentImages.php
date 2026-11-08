<?php

namespace App\Console\Commands;

use App\Models\Image;
use App\Services\ImageRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class IndexContentImages extends Command
{
    protected $signature = 'images:index {--activate : Enable relation-based cleanup after indexing}';

    protected $description = 'Register legacy editor files and build image usage relations without deleting files';

    public function handle(ImageRegistry $registry): int
    {
        if (! app()->isDownForMaintenance() && ! app()->environment('testing')) {
            $this->error('Run in maintenance mode with queue workers stopped to obtain a consistent snapshot.');

            return self::FAILURE;
        }

        Image::withTrashed()->chunkById(200, function ($images): void {
            foreach ($images as $image) {
                $image->save();
            }
        });

        $disk = (string) config('filesystems.media');
        foreach (Storage::disk($disk)->listContents('galleries/content', true) as $entry) {
            if ($entry->isFile()) {
                $image = $registry->register($disk, $entry->path(), $entry->fileSize());
                // Existing unreferenced uploads get a fresh grace period after discovery.
                $this->line('Registered image '.$image->id);
            }
        }

        foreach (ImageRegistry::FIELDS as $model => $fields) {
            $model::query()->select(['id', ...$fields])->chunkById(200, function ($items) use ($registry): void {
                foreach ($items as $item) {
                    DB::transaction(fn () => $registry->sync($item, clean: false));
                }
            });
        }

        if ($this->option('activate')) {
            DB::table('image_registry_state')->updateOrInsert(['id' => 1], ['indexed_at' => now()]);
        }
        $this->info('Image registry indexed. No files were deleted.');

        return self::SUCCESS;
    }
}
