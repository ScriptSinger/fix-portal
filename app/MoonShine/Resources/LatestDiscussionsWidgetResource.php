<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\UI\Fields\Number;

#[Icon('chat-bubble-left-right')]
#[Group('Виджеты', 'squares-2x2')]
#[Order(4)]
final class LatestDiscussionsWidgetResource extends WidgetResource
{
    public function getTitle(): string
    {
        return 'Последние обсуждения';
    }

    protected function widgetKey(): string
    {
        return 'latest_discussions';
    }

    protected function widgetFields(): iterable
    {
        return [
            Number::make('Количество сообщений', 'display_limit')
                ->min(3)
                ->max(10)
                ->hint('Сколько последних комментариев и ответов показывать.'),
        ];
    }

    protected function rules($item): array
    {
        return parent::rules($item) + [
            'display_limit' => ['required', 'integer', 'between:3,10'],
        ];
    }
}
