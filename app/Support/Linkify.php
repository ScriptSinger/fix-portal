<?php

namespace App\Support;

/**
 * Адреса в тексте сообщений (набранный текст хранится экранированным, без разметки) делает ссылками при показе.
 * Работает по уже безопасному HTML: правит только текст между тегами, поэтому адреса внутри атрибутов (src у фото)
 * и внутри готовых ссылок остаются как есть. Берутся только http и https.
 */
class Linkify
{
    public static function html(?string $html): string
    {
        $html = (string) $html;

        if (! str_contains($html, 'http')) {
            return $html;
        }

        $parts = preg_split('/(<[^>]*>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) ?: [$html];
        $insideLink = 0;

        foreach ($parts as $i => $part) {
            if ($part[0] === '<') {
                if (preg_match('/^<a\b/i', $part)) {
                    $insideLink++;
                } elseif (preg_match('/^<\/a\s*>/i', $part)) {
                    $insideLink = max(0, $insideLink - 1);
                }

                continue;
            }

            if ($insideLink === 0) {
                $parts[$i] = self::links($part);
            }
        }

        return implode('', $parts);
    }

    private static function links(string $text): string
    {
        // адрес кончается на пробеле, скобке, кавычке (в том числе экранированной) или знаке препинания в конце предложения
        return preg_replace_callback('~https?://(?:(?!&quot;|&#0?39;|&lt;|&gt;)[^\s<>"\'])+~iu', function (array $m) {
            $url = $m[0];
            $tail = '';

            while ($url !== '' && preg_match('/[.,;:!?)\]]$/u', $url)) {
                $tail = mb_substr($url, -1) . $tail;
                $url = mb_substr($url, 0, -1);
            }

            if ($url === '') {
                return $m[0];
            }

            return '<a href="' . $url . '" target="_blank" rel="nofollow noopener noreferrer ugc">' . $url . '</a>' . $tail;
        }, $text) ?? $text;
    }
}
