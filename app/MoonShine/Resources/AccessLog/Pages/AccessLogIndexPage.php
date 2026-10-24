<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\AccessLog\Pages;

use App\MoonShine\Resources\AccessLog\AccessLogResource;
use App\Support\AccessLogReader;
use Illuminate\Support\Collection;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Crud\QueryTags\QueryTag;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<AccessLogResource>
 */
class AccessLogIndexPage extends IndexPage
{
    protected bool $isLazy = false;

    protected function filters(): iterable
    {
        $files = app(AccessLogReader::class)->fileNames();

        return [
            Select::make('Файл', 'file')
                ->options(array_combine($files, $files) ?: [])
                ->nullable(),
            Date::make('С даты', 'date_from'),
            Date::make('По дату', 'date_to'),
            Text::make('Путь содержит', 'path'),
            Text::make('IP содержит', 'ip'),
            Text::make('Исключить IP', 'exclude_ip')->hint('Точные адреса через запятую или пробел'),
            Switcher::make('Скрыть мой IP', 'hide_my_ip'),
            Text::make('User-Agent содержит', 'user_agent'),
            Switcher::make('Без ботов', 'no_bots'),
            Switcher::make('Только боты', 'only_bots'),
            Switcher::make('Только подозрительные', 'only_suspicious'),
            Switcher::make('Только медленные', 'only_slow'),
        ];
    }

    /**
     * @return list<QueryTag>
     */
    protected function queryTags(): array
    {
        $status = static fn (callable $test) => static fn (Collection $rows): Collection => $rows
            ->filter(static fn (array $row): bool => $test($row['status']))
            ->values();

        return [
            QueryTag::make('Все', static fn (Collection $rows): Collection => $rows)
                ->alias('all')
                ->default(),
            QueryTag::make('200', $status(static fn (int $s): bool => $s === 200))->alias('200'),
            QueryTag::make('3xx', $status(static fn (int $s): bool => intdiv($s, 100) === 3))->alias('3xx'),
            QueryTag::make('403', $status(static fn (int $s): bool => $s === 403))->alias('403'),
            QueryTag::make('404', $status(static fn (int $s): bool => $s === 404))->alias('404'),
            QueryTag::make('429', $status(static fn (int $s): bool => $s === 429))->alias('429'),
            QueryTag::make('4xx', $status(static fn (int $s): bool => intdiv($s, 100) === 4))->alias('4xx'),
            QueryTag::make('5xx', $status(static fn (int $s): bool => intdiv($s, 100) === 5))->alias('5xx'),
        ];
    }

    /**
     * Opts the table into public/assets/moonshine/resizable-table.{js,css}: columns can be
     * dragged wider, and the width (not MoonShine's 24ch/48ch caps) decides how
     * much of a value is shown, so a client sending a huge User-Agent cannot
     * stretch the layout.
     *
     * @param  \MoonShine\UI\Components\Table\TableBuilder  $component
     */
    protected function modifyListComponent(ComponentContract $component): ComponentContract
    {
        return $component->customAttributes([
            'data-resizable' => 'access-logs',
            'data-resizable-defaults' => json_encode([
                'time' => 150,
                'method' => 80,
                'path' => 320,
                'status' => 80,
                'duration_ms' => 70,
                'is_slow' => 70,
                'is_bot' => 70,
                'is_suspicious' => 110,
                'ip' => 130,
                'referer' => 220,
                'user_agent' => 420,
            ]),
        ]);
    }
}
