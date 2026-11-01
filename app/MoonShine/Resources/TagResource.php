<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Tag;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

#[Icon('tag')]
#[Group('Статьи', 'newspaper')]
#[Order(3)]
/**
 * @extends ModelResource<Tag>
 */
class TagResource extends BaseResource
{
    protected string $model = Tag::class;

    protected string $column = 'title';

    protected bool $simplePaginate = true;

    public function getTitle(): string
    {
        return 'Теги';
    }

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название', 'title')->sortable()->link(
                static fn (string $value, Text $field): string => route(
                    'tags.show',
                    ['tag' => $field->getData()?->getOriginal()->slug],
                ),
                icon: 'arrow-top-right-on-square',
                blank: true,
            ),
            Text::make('Slug', 'slug'),
        ];
    }

    protected function detailFields(): iterable
    {
        return $this->indexFields();
    }

    protected function formFields(): iterable
    {
        return [
            ID::make(),
            Text::make('Название', 'title')->required(),
        ];
    }

    protected function rules($item): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
        ];
    }

    protected function search(): array
    {
        return ['id', 'title'];
    }
}
