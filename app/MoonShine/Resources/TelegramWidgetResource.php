<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\UI\Fields\Text;

#[Icon('paper-airplane')]
#[Group('Виджеты', 'squares-2x2')]
#[Order(3)]
final class TelegramWidgetResource extends WidgetResource
{
    public function getTitle(): string
    {
        return 'Telegram-чат';
    }

    protected function widgetKey(): string
    {
        return 'telegram';
    }

    protected function widgetFields(): iterable
    {
        return [
            Text::make('Ссылка на чат', 'url')->required()->hint('Полная ссылка, например https://t.me/ufamasters_chat'),
        ];
    }

    protected function rules($item): array
    {
        return parent::rules($item) + [
            'url' => ['required', 'url', 'max:255'],
        ];
    }
}
