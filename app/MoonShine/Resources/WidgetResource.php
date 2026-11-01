<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\SiteWidget;
use Illuminate\Contracts\Database\Eloquent\Builder;
use MoonShine\Support\Enums\Action;
use MoonShine\Support\ListOf;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/** @extends BaseResource<SiteWidget> */
abstract class WidgetResource extends BaseResource
{
    protected string $model = SiteWidget::class;

    protected string $column = 'title';

    protected function modifyQueryBuilder(Builder $builder): Builder
    {
        return $builder->where('key', $this->widgetKey());
    }

    protected function activeActions(): ListOf
    {
        return parent::activeActions()->only(Action::UPDATE);
    }

    protected function indexFields(): iterable
    {
        return [
            ID::make(),
            Text::make('Виджет', 'title')->readonly(),
            Switcher::make('Включён', 'enabled')->updateOnPreview(),
            Text::make('Описание', 'description')->readonly(),
        ];
    }

    protected function formFields(): iterable
    {
        return [
            ID::make(),
            Text::make('Виджет', 'title')->readonly(),
            Text::make('Назначение', 'description')->readonly(),
            Switcher::make('Включён', 'enabled')
                ->hint('Отключение убирает виджет с публичных страниц, но не удаляет его настройки.'),
            ...$this->widgetFields(),
        ];
    }

    /** @return iterable */
    protected function widgetFields(): iterable
    {
        return [];
    }

    abstract protected function widgetKey(): string;

    protected function rules($item): array
    {
        return ['enabled' => ['boolean']];
    }
}
