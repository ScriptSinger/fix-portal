<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\Storage;

final class ContentImageCleanupService
{
    /** @return list<string> */
    public function urlsFromHtml(?string $html): array
    {
        preg_match_all('/<img\b[^>]*>/i', (string) $html, $tags);

        $urls = [];
        foreach ($tags[0] ?? [] as $tag) {
            if (preg_match('/\bsrc=["\']([^"\']+)["\']/i', $tag, $src)) {
                $urls[] = html_entity_decode($src[1]);
            }
            if (preg_match('/\bdata-full=["\']([^"\']+)["\']/i', $tag, $full)) {
                $urls[] = html_entity_decode($full[1]);
            }
        }

        return array_values(array_unique($urls));
    }

    /** @param list<string> $urls */
    public function cleanup(array $urls): void
    {
        foreach (array_unique($urls) as $url) {
            if ($this->isReferenced($url)) {
                continue;
            }

            $path = $this->contentPath($url);
            if ($path !== null) {
                Storage::disk((string) config('filesystems.media'))->delete($path);
            }
        }
    }

    private function isReferenced(string $url): bool
    {
        return Post::query()
            ->where('content', 'like', '%'.$url.'%')
            ->orWhere('description', 'like', '%'.$url.'%')
            ->exists();
    }

    private function contentPath(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (! is_string($path)) {
            return null;
        }

        $marker = 'galleries/content/';
        $position = strpos($path, $marker);

        return $position === false ? null : substr($path, $position);
    }
}
