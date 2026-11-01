<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Image;
use App\Models\Question;
use App\Models\Reply;
use App\Support\UserImages;
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
        if ($ids === []) {
            return;
        }

        Image::withTrashed()->whereIn('id', $ids)->get()->each(function (Image $image): void {
            if ($this->isReferenced($image)) {
                return;
            }

            $this->deleteFile($image->url);
            $image->forceDelete();
        });
    }

    public function deleteFile(?string $url): void
    {
        $path = parse_url((string) $url, PHP_URL_PATH);
        $path = is_string($path) ? preg_replace('#^/?storage/#', '', $path) : null;

        if (! is_string($path) || ! str_starts_with($path, 'images/users/')) {
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
