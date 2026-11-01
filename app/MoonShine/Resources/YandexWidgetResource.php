<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\UI\Fields\Text;

#[Icon('megaphone')]
#[Group('Виджеты', 'squares-2x2')]
#[Order(1)]
final class YandexWidgetResource extends WidgetResource
{
    public function getTitle(): string
    {
        return 'РСЯ';
    }

    protected function widgetKey(): string
    {
        return 'ads';
    }

    protected function widgetFields(): iterable
    {
        return [
            Text::make('ID рекламного блока', 'block_id')
                ->required()
                ->hint('Например: R-A-1966455-2'),
        ];
    }

    protected function rules($item): array
    {
        return parent::rules($item) + [
            'block_id' => ['required', 'string', 'max:100'],
        ];
    }
}
