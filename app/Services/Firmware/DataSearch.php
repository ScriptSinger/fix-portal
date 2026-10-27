<?php

namespace App\Services\Firmware;

use App\Support\SqlLike;
use Illuminate\Database\Eloquent\Builder;

/** Поиск значения в таблице параметров дампа (поле data): по строке «название | значение» для известных названий. */
class DataSearch
{
    public const LABELS = ['Модель', 'Model', 'S/N', 'SN', 'Серийный номер', 'PNS', 'PNC', 'P/N', 'Part Number', 'Код прошивки', 'Firmware Code', 'Для S/N'];

    /** Добавляет условия OR к уже открытой группе условий. */
    public static function orWhereMatches(Builder $query, string $term): void
    {
        $escaped = SqlLike::escape($term);

        foreach (self::LABELS as $label) {
            $query->orWhereRaw("data LIKE ? ESCAPE '\\\\'", ['%<td>' . $label . '</td>%<td>' . $escaped . '%</td>%']);
        }
    }
}
