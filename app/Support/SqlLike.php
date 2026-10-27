<?php

namespace App\Support;

/** Экранирование значения для LIKE: символы % и _ в поиске должны искаться как обычные. */
class SqlLike
{
    public static function escape(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
