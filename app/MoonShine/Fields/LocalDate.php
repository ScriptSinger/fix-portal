<?php

declare(strict_types=1);

namespace App\MoonShine\Fields;

use Carbon\Carbon;
use DateTimeInterface;
use MoonShine\UI\Fields\Date;

/**
 * Дата и время в списках и карточках админки по местному времени (config app.display_timezone).
 * В базе всё лежит в UTC, поэтому без перевода запись, сделанная в 01:27 по Уфе, показывалась бы как 20:27 вчерашнего дня.
 */
class LocalDate extends Date
{
    protected function resolvePreview(): string
    {
        $value = $this->toFormattedValue();

        if (blank($value)) {
            return '';
        }

        $date = $value instanceof DateTimeInterface ? Carbon::instance($value) : Carbon::parse((string) $value);

        return $date->timezone(config('app.display_timezone'))->format($this->getFormat());
    }
}
