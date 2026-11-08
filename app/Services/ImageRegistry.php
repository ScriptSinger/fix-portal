<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Image;
use App\Models\Post;
use App\Models\Question;
use App\Models\Reply;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class ImageRegistry
{
    public const FIELDS = [Post::class => ['content', 'description'], Question::class => ['description'], Comment::class => ['text'], Reply::class => ['text']];

    public function ready(): bool
    {
        return DB::table('image_registry_state')->where('id', 1)->whereNotNull('indexed_at')->exists();
    }

    /** Only application-owned URLs are mapped to storage keys. Never trust a path substring on a foreign host. */
    public function location(string $url): ?array
    {
        foreach (array_unique(['public', (string) config('filesystems.media')]) as $disk) {
            foreach (['images/users/', 'images/telegram/', 'galleries/content/'] as $prefix) {
                $base = Storage::disk($disk)->url($prefix);
                $candidates = [$base];
                if ($disk === 'public') {
                    $candidates[] = '/storage/'.$prefix;
                }
                foreach ($candidates as $candidate) {
                    if (! str_starts_with($url, $candidate)) {
                        continue;
                    }
                    $path = $prefix.substr($url, strlen($candidate));
                    if (str_contains($path, '..') || str_contains($path, '%') || str_contains($path, '?') || str_contains($path, '#')) {
                        return null;
                    }

                    return ['disk' => $disk, 'path' => $path];
                }
            }
        }

        return null;
    }

    public function register(string $disk, string $path, ?int $size = null, ?string $mime = null): Image
    {
        $url = Storage::disk($disk)->url($path);
        $existing = Image::withTrashed()->where('disk', $disk)->where('path', $path)->first()
            ?? Image::withTrashed()->where('url', $url)->first();
        if ($existing) {
            $existing->forceFill(['disk' => $disk, 'path' => $path])->save();

            return $existing;
        }

        return Image::forceCreate(['url' => $url, 'disk' => $disk, 'path' => $path, 'size' => $size ?? 0, 'mime' => $mime ?? 'application/octet-stream']);
    }

    public function ids(string $html): array
    {
        $ids = [];
        foreach (app(ContentImageCleanupService::class)->urlsFromHtml($html) as $url) {
            $image = Image::withTrashed()->where('url', $url)->first();
            $location = $this->location($url);
            if (! $image && $location) {
                $image = Image::withTrashed()->where($location)->first();
                if (! $image && str_starts_with($location['path'], 'galleries/content/')) {
                    $image = $this->register($location['disk'], $location['path']);
                }
            }
            if ($image) {
                $ids[] = $image->id;
            }
        }

        return array_values(array_unique($ids));
    }

    public function sync(Model $model, bool $clean = true): void
    {
        $scope = DB::table('imageables')->where('imageable_type', $model->getMorphClass())->where('imageable_id', $model->getKey());
        $old = (clone $scope)->pluck('image_id')->all();
        foreach (self::FIELDS[$model::class] as $field) {
            if (! array_key_exists($field, $model->getAttributes())) {
                continue;
            }
            $ids = $this->ids((string) $model->getAttribute($field));
            // The same image locks are used by pruning, so an image cannot be attached during deletion.
            $locked = Image::withTrashed()->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->pluck('id')->all();
            if (count($locked) !== count($ids)) {
                throw new \RuntimeException('An image was removed while saving. Please upload it again.');
            }
            (clone $scope)->where('field', $field)->whereNotIn('image_id', $ids)->delete();
            foreach ($ids as $id) {
                DB::table('imageables')->insertOrIgnore(['image_id' => $id, 'imageable_type' => $model->getMorphClass(), 'imageable_id' => $model->getKey(), 'field' => $field]);
            }
        }
        if ($clean) {
            app(ImageCleanupService::class)->cleanup($old);
        }
    }

    public function detach(Model $model): void
    {
        $query = DB::table('imageables')->where('imageable_type', $model->getMorphClass())->where('imageable_id', $model->getKey());
        $ids = (clone $query)->pluck('image_id')->all();
        $query->delete();
        app(ImageCleanupService::class)->cleanup($ids);
    }
}
