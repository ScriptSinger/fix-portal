<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Image;
use App\Models\Question;
use App\Models\Reply;
use App\Support\UserImages;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class ImageCleanupService
{
    /** @return list<int> */
    public function idsFromHtml(?string $html): array
    {
        preg_match_all('/data-id=["\'](\d+)["\']/i', (string) $html, $matches);
        preg_match_all('/<img\b[^>]*>/i', (string) $html, $tags);

        $urls = [];
        foreach ($tags[0] ?? [] as $tag) {
            foreach (['src', 'data-full'] as $attribute) {
                if (preg_match('/\b'.$attribute.'=["\']([^"\']+)["\']/i', $tag, $url)) {
                    $urls[] = html_entity_decode($url[1]);
                }
            }
        }

        $ids = array_map('intval', $matches[1] ?? []);
        if ($urls !== []) {
            $ids = array_merge($ids, Image::withTrashed()->whereIn('url', array_unique($urls))->pluck('id')->all());
        }

        return array_values(array_unique(array_map('intval', $ids)));
    }

    /** @param list<int> $ids */
    public function cleanup(array $ids): void
    {
        DB::afterCommit(fn () => $this->cleanupCommitted($ids));
    }

    private function cleanupCommitted(array $ids): void
    {
        if ($ids === []) {
            return;
        }

        foreach (array_unique($ids) as $id) {
            DB::transaction(function () use ($id): void {
                $image = Image::withTrashed()->whereKey($id)->lockForUpdate()->first();
                if ($image && ! $this->isReferenced($image)) {
                    $image->forceDelete();
                }
            });
        }
    }

    public function deleteStoredFile(Image $image): void
    {
        if (! $image->disk || ! $image->path) {
            $this->deleteFile($image->url);

            return;
        }
        $disk = $image->disk;
        $path = $image->path;
        DB::afterCommit(function () use ($disk, $path): void {
            if (Image::withTrashed()->where('disk', $disk)->where('path', $path)->exists()) {
                return;
            }
            Storage::disk($disk)->delete($path);
            if ($disk === 'public' && str_starts_with($path, 'images/')) {
                foreach (UserImages::WIDTHS as $width) {
                    Storage::disk($disk)->delete(UserImages::variantPath($path, $width));
                }
            }
        });
    }

    public function deleteFile(?string $url): void
    {
        DB::afterCommit(fn () => $this->deleteCommittedFile($url));
    }

    private function deleteCommittedFile(?string $url): void
    {
        $path = parse_url((string) $url, PHP_URL_PATH);
        $path = is_string($path) ? preg_replace('#^/?storage/#', '', $path) : null;

        if (! is_string($path)
            || (! str_starts_with($path, 'images/users/') && ! str_starts_with($path, 'images/telegram/'))) {
            return;
        }

        $disk = Storage::disk('public');
        $disk->delete($path);

        foreach (UserImages::WIDTHS as $width) {
            $disk->delete(UserImages::variantPath($path, $width));
        }
    }

    private function isReferenced(Image $image): bool
    {
        if (app(ImageRegistry::class)->ready()) {
            return DB::table('imageables')->where('image_id', $image->id)->exists();
        }

        $id = (string) $image->getKey();
        $url = (string) $image->url;

        return Comment::query()->where(function ($query) use ($id, $url): void {
            $query->where('text', 'like', '%data-id="'.$id.'"%')
                ->orWhere('text', 'like', "%data-id='".$id."'%");
            if ($url !== '') {
                $query->orWhere('text', 'like', '%'.$url.'%');
            }
        })->exists()
            || Reply::query()->where(function ($query) use ($id, $url): void {
                $query->where('text', 'like', '%data-id="'.$id.'"%')
                    ->orWhere('text', 'like', "%data-id='".$id."'%");
                if ($url !== '') {
                    $query->orWhere('text', 'like', '%'.$url.'%');
                }
            })->exists()
            || Question::query()->where(function ($query) use ($id, $url): void {
                $query->where('description', 'like', '%data-id="'.$id.'"%')
                    ->orWhere('description', 'like', "%data-id='".$id."'%");
                if ($url !== '') {
                    $query->orWhere('description', 'like', '%'.$url.'%');
                }
            })->exists();
    }
}
