<?php

namespace App\Support;

use App\Models\Image;

/**
 * Текст сообщения или вопроса: набранный текст экранируется, загруженные фото добавляются тегами img.
 * Берутся только фото, которые загрузил сам автор (таблица images); адреса из $keep (фото, уже стоявшие в сохранённом тексте) тоже остаются.
 */
class MessageBody
{
    /**
     * @param list<string> $urls
     * @param list<string> $keep
     */
    public static function make(?string $text, array $urls, int $userId, array $keep = []): string
    {
        $out = e(trim((string) $text));

        $owned = $urls === [] ? collect() : Image::query()->where('user_id', $userId)->whereIn('url', $urls)->get()->keyBy('url');
        foreach (array_unique($urls) as $url) {
            if ($image = $owned->get($url)) {
                $out .= '<img src="' . e($url) . '" class="img-fluid" data-id="' . $image->id . '">';
            } elseif (in_array($url, $keep, true)) {
                $out .= '<img src="' . e($url) . '" class="img-fluid">';
            }
        }

        return $out;
    }

    /** Текст сохранённого описания для поля ввода: абзацы и переносы остаются переводами строк, остальная разметка отбрасывается. */
    public static function toPlain(?string $html): string
    {
        $text = preg_replace('~<(br\s*/?|/p|/div|/li)>~i', "\n", (string) $html);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace("/\n{3,}/", "\n\n", str_replace("\r", '', $text)));
    }
}
