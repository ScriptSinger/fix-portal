<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Appliance;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

#[Icon('wrench-screwdriver')]
#[Group('Каталог', 'rectangle-stack')]
#[Order(5)]
/**
 * @extends ModelResource<Appliance>
 */
class ApplianceResource extends BaseResource
{
    protected string $model = Appliance::class;

    protected string $column = 'title';

    protected bool $simplePaginate = true;

    public function getTitle(): string
    {
        return 'Техника';
    }

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название', 'title')->sortable(),
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
