<?php

namespace App\Support;

use App\Models\Image;

/**
 * Текст сообщения в обсуждении: набранный текст экранируется, загруженные фото добавляются тегами img.
 * Берутся только фото, которые загрузил сам автор (таблица images), чужие адреса игнорируются.
 */
class MessageBody
{
    /** @param list<string> $urls */
    public static function make(?string $text, array $urls, int $userId): string
    {
        $text = e(trim((string) $text));

        $images = $urls === [] ? collect() : Image::query()->where('user_id', $userId)->whereIn('url', $urls)->get()->sortBy(fn (Image $i) => array_search($i->url, $urls, true));
        foreach ($images as $image) {
            $text .= '<img src="' . e($image->url) . '" class="img-fluid" data-id="' . $image->id . '">';
        }

        return $text;
    }
}
