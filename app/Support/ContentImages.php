<?php

namespace App\Support;

/**
 * Картинки из редактора часто сохраняются без alt: скринридер их не описывает, а поисковик хуже понимает страницу.
 * Добавляет alt только там, где его нет совсем (пустой alt="" считается осознанным решением автора).
 */
class ContentImages
{
    public static function withAlt(?string $html, string $subject): string
    {
        $html = self::withVariants((string) $html);
        $n = 0;

        return preg_replace_callback('/<img\b(?![^>]*\balt\s*=)([^>]*)>/i', function (array $m) use ($subject, &$n) {
            $n++;
            $alt = e("{$subject} — иллюстрация {$n}");

            return '<img alt="'.$alt.'"'.$m[1].'>';
        }, $html) ?? $html;
    }

    /** Фото пользователей в тексте заменяет копией 1024 px; оригинал остаётся в data-full для окна просмотра. */
    public static function withVariants(string $html): string
    {
        return preg_replace_callback('~<img\b([^>]*?)\bsrc="([^"]*/storage/images/users/[^"]+)"([^>]*)>~i', function (array $m) {
            $small = UserImages::url($m[2], 1024);
            if ($small === $m[2] || str_contains($m[0], 'data-full=')) {
                return $m[0];
            }

            return '<img' . $m[1] . 'src="' . e($small) . '" data-full="' . e($m[2]) . '"' . $m[3] . '>';
        }, $html) ?? $html;
    }
}
