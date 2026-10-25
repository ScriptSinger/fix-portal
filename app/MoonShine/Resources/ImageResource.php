<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Image;
use MoonShine\Support\Enums\Action;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\MenuManager\Attributes\SkipMenu;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Support\ListOf;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Text;

#[Icon('photo')]
#[Group('Пользователи', 'user-group')]
#[SkipMenu]
#[Order(8)]
/**
 * Изображения, загруженные пользователями (только просмотр и удаление).
 *
 * @extends ModelResource<Image>
 */
class ImageResource extends BaseResource
{
    protected string $model = Image::class;

    protected string $column = 'url';

    protected array $with = ['user'];

    protected bool $simplePaginate = true;

    protected string $sortColumn = 'id';

    public function getTitle(): string
    {
        return 'Изображения';
    }

    protected function activeActions(): ListOf
    {
        return parent::activeActions()->only(Action::VIEW, Action::DELETE, Action::MASS_DELETE);
    }

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Preview::make('Картинка', 'url')->image(),
            BelongsTo::make('Пользователь', 'user', 'name', UserResource::class),
            Text::make('MIME', 'mime'),
            Preview::make('Размер', 'size', fn (Image $i) => $i->size_mb),
            Date::make('Загружено', 'created_at')->format('d.m.Y H:i')->sortable(),
        ];
    }

    protected function detailFields(): iterable
    {
        return $this->indexFields();
    }

    protected function search(): array
    {
        return ['id', 'url'];
    }
}
