<?php

namespace App\Support;

/**
 * Старые вопросы и ответы сохранялись из редактора вместе с копипастой из чатов и сайтов: в тексте остались
 * inline-стили с чужими шрифтами, размерами и цветами (font-size: 18px, font-family: Söhne). Из-за них текст выглядит
 * крупнее соседних. При показе убираем стили, размеры и шрифты, структура (абзацы, списки, ссылки, выделение) остаётся.
 */
class HtmlCleanup
{
    public static function stripInlineStyles(string $html): string
    {
        // style="..." и style='...' у любых тегов
        $html = preg_replace('/\s+style\s*=\s*("[^"]*"|\'[^\']*\')/iu', '', $html) ?? $html;
        // устаревшие атрибуты оформления у <font>: размер, гарнитура, цвет
        do {
            $before = $html;
            $html = preg_replace('/(<font\b[^>]*?)\s+(?:face|size|color)\s*=\s*("[^"]*"|\'[^\']*\')/iu', '$1', $html) ?? $html;
        } while ($html !== $before);

        return $html;
    }
}
