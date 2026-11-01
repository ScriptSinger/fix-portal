<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\UI\Fields\Text;

#[Icon('wrench-screwdriver')]
#[Group('Виджеты', 'squares-2x2')]
#[Order(2)]
final class RepairWidgetResource extends WidgetResource
{
    public function getTitle(): string
    {
        return 'Ремонт';
    }

    protected function widgetKey(): string
    {
        return 'repair';
    }

    protected function widgetFields(): iterable
    {
        return [
            Text::make('Ссылка на сервис', 'url')->required()->hint('Ссылка на страницу партнёрского сервиса.'),
        ];
    }

    protected function rules($item): array
    {
        return parent::rules($item) + [
            'url' => ['required', 'url', 'max:255'],
        ];
    }
}
