<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\RequestLog;
use MoonShine\Support\Enums\Action;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Support\ListOf;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;

#[Icon('clipboard-document-list')]
#[Group('Безопасность', 'shield-check')]
#[Order(9)]
/**
 * @extends ModelResource<RequestLog>
 */
class RequestLogResource extends BaseResource
{
    protected string $model = RequestLog::class;

    protected string $column = 'route';

    protected bool $simplePaginate = true;

    protected string $sortColumn = 'id';

    public function getTitle(): string
    {
        return 'Логи запросов';
    }

    protected function activeActions(): ListOf
    {
        return parent::activeActions()->only(Action::VIEW, Action::DELETE, Action::MASS_DELETE);
    }

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Date::make('Время', 'created_at')->format('d.m.Y H:i:s')->sortable(),
            Text::make('Метод', 'methodType'),
            Text::make('Маршрут', 'route'),
            Number::make('Статус', 'status')->sortable(),
            Text::make('IP', 'ipAddress')->sortable(),
            Text::make('User-Agent', 'userAgent'),
        ];
    }

    protected function detailFields(): iterable
    {
        return [
            ...$this->indexFields(),
            Text::make('Referer', 'referer'),
            Text::make('Язык', 'locale'),
            Number::make('ID пользователя', 'user_id'),
        ];
    }

    protected function search(): array
    {
        return ['route', 'ipAddress', 'userAgent'];
    }

    protected function filters(): iterable
    {
        return [
            Text::make('IP', 'ipAddress'),
            Text::make('Метод', 'methodType'),
            Number::make('Статус', 'status'),
        ];
    }
}
