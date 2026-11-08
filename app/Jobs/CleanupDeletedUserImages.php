<?php

namespace App\Jobs;

use App\Models\Image;
use App\Support\UserImages;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class CleanupDeletedUserImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 5;

    public function __construct(public array $imageIds, public ?string $avatarPath) {}

    public function backoff(): array
    {
        return [60, 300, 900, 3600];
    }

    public function handle(): void
    {
        foreach (array_unique($this->imageIds) as $id) {
            DB::transaction(function () use ($id): void {
                $image = Image::withTrashed()->whereKey($id)->lockForUpdate()->first();
                if (! $image || DB::table('imageables')->where('image_id', $id)->exists()) {
                    return;
                }
                if (! $image->disk || ! $image->path) {
                    throw new RuntimeException("Image {$id} has no registered storage location.");
                }
                $other = Image::withTrashed()->where('disk', $image->disk)->where('path', $image->path)->where('id', '!=', $id)->exists();
                if (! $other) {
                    $disk = Storage::disk($image->disk);
                    $paths = [$image->path];
                    if ($image->disk === 'public' && str_starts_with($image->path, 'images/')) {
                        foreach (UserImages::WIDTHS as $width) {
                            $paths[] = UserImages::variantPath($image->path, $width);
                        }
                    }
                    foreach ($paths as $path) {
                        if ($disk->exists($path) && ! $disk->delete($path)) {
                            throw new RuntimeException("Unable to delete image file: {$path}");
                        }
                    }
                }
                $image->forceDeleteQuietly();
            });
        }
        if ($this->avatarPath && ! DB::table('avatars')->where('uri', $this->avatarPath)->exists()) {
            $disk = Storage::disk('public');
            if ($disk->exists($this->avatarPath) && ! $disk->delete($this->avatarPath)) {
                throw new RuntimeException('Unable to delete avatar.');
            }
        }
    }
}
