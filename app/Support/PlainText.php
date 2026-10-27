<?php

namespace App\Support;

use Illuminate\Support\Str;

/** Короткий простой текст из HTML: для цитат, уведомлений и разметки поисковиков. */
class PlainText
{
    public static function from(?string $html, int $limit = 90): string
    {
        $text = html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return Str::limit(trim(preg_replace('/\s+/u', ' ', $text)), $limit);
    }
}
