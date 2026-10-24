<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Cta;
use MoonShine\MenuManager\Attributes\SkipMenu;
use MoonShine\Support\Attributes\Icon;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use MoonShine\UI\Fields\Url;

#[Icon('megaphone')]
#[SkipMenu]
/**
 * CTA-блоки статей. Управляются со страницы поста через HasMany.
 *
 * @extends ModelResource<Cta>
 */
class CtaResource extends BaseResource
{
    protected string $model = Cta::class;

    protected string $column = 'title';

    public function getTitle(): string
    {
        return 'CTA';
    }

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Заголовок', 'title'),
            Text::make('Размещение', 'placement'),
            Number::make('Приоритет', 'priority')->sortable(),
            Switcher::make('Активен', 'is_active'),
        ];
    }

    protected function formFields(): iterable
    {
        return [
            ID::make(),
            Url::make('URL посадочной', 'target_url')->required(),
            Text::make('Заголовок', 'title')->required(),
            Textarea::make('Текст', 'text'),
            Text::make('Текст ссылки', 'anchor')->required(),
            Select::make('Размещение', 'placement')->options([
                'middle' => 'В середине',
                'end' => 'В конце',
                'sidebar' => 'В сайдбаре',
            ])->required(),
            Number::make('Приоритет', 'priority')->default(0),
            Text::make('Город', 'city'),
            Text::make('Бренд', 'brand'),
            Text::make('Тип техники', 'appliance_type'),
            Text::make('Проблема', 'problem'),
            Switcher::make('Активен', 'is_active')->default(true),
        ];
    }

    protected function rules($item): array
    {
        return [
            'target_url' => ['required', 'url', 'max:2048'],
            'title' => ['required', 'string', 'max:255'],
            'text' => ['nullable', 'string'],
            'anchor' => ['required', 'string', 'max:255'],
            'placement' => ['required', 'in:middle,end,sidebar'],
            'priority' => ['nullable', 'integer', 'min:0'],
            'city' => ['nullable', 'string', 'max:64'],
            'brand' => ['nullable', 'string', 'max:64'],
            'appliance_type' => ['nullable', 'string', 'max:64'],
            'problem' => ['nullable', 'string', 'max:64'],
            'is_active' => ['boolean'],
        ];
    }
}
