<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Category;
use App\MoonShine\Fields\LocalDate;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

#[Icon('folder')]
#[Group('Статьи', 'newspaper')]
#[Order(2)]
/**
 * @extends ModelResource<Category>
 */
class CategoryResource extends BaseResource
{
    protected string $model = Category::class;

    protected string $column = 'title';

    protected bool $simplePaginate = true;

    public function getTitle(): string
    {
        return 'Категории';
    }

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название', 'title')->sortable()->link(
                static fn (string $value, Text $field): string => route(
                    'categories.show',
                    ['category' => $field->getData()?->getOriginal()->slug],
                ),
                icon: 'arrow-top-right-on-square',
                blank: true,
            ),
            Text::make('Slug', 'slug'),
            LocalDate::make('Создана', 'created_at')->format('d.m.Y')->sortable(),
        ];
    }

    protected function detailFields(): iterable
    {
        return [
            ...$this->indexFields(),
            Textarea::make('Описание', 'description'),
        ];
    }

    protected function formFields(): iterable
    {
        return [
            ID::make(),
            Text::make('Название', 'title')->required(),
            Textarea::make('Описание', 'description'),
        ];
    }

    protected function rules($item): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    protected function search(): array
    {
        return ['id', 'title'];
    }
}
