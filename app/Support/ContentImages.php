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
        $html = (string) $html;
        $n = 0;

        return preg_replace_callback('/<img\b(?![^>]*\balt\s*=)([^>]*)>/i', function (array $m) use ($subject, &$n) {
            $n++;
            $alt = e("{$subject} — иллюстрация {$n}");

            return '<img alt="'.$alt.'"'.$m[1].'>';
        }, $html) ?? $html;
    }
}
