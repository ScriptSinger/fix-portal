<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Firmware;
use MoonShine\Support\Enums\Action;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Support\ListOf;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

#[Icon('cpu-chip')]
#[Group('Каталог', 'rectangle-stack')]
#[Order(6)]
/**
 * @extends ModelResource<Firmware>
 */
class FirmwareResource extends BaseResource
{
    protected string $model = Firmware::class;

    protected string $column = 'title';

    protected bool $simplePaginate = true;

    protected string $sortColumn = 'id';

    public function getTitle(): string
    {
        return 'Прошивки';
    }

    protected function activeActions(): ListOf
    {
        return parent::activeActions()->except(Action::CREATE);
    }

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название', 'title')->sortable(),
            Text::make('Платформа', 'platform')->sortable(),
            Text::make('Формат', 'extension'),
            Number::make('Размер', 'size')->sortable(),
            Date::make('Дата', 'date')->format('d.m.Y')->sortable(),
            Number::make('Просмотры', 'views_count')->sortable(),
            Number::make('Скачивания', 'downloads_count')->sortable(),
        ];
    }

    protected function detailFields(): iterable
    {
        return [
            ...$this->indexFields(),
            Text::make('CRC32', 'crc32'),
            Textarea::make('Данные', 'data')->unescape(),
        ];
    }

    protected function formFields(): iterable
    {
        return [
            ID::make(),
            Text::make('Название', 'title')->required(),
            Text::make('Платформа', 'platform'),
            Text::make('Формат', 'extension'),
            Text::make('CRC32', 'crc32'),
            Date::make('Дата', 'date'),
            Textarea::make('Данные (HTML)', 'data')->customAttributes(['rows' => 14]),
        ];
    }

    protected function rules($item): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:255'],
            'extension' => ['nullable', 'string', 'max:255'],
            'crc32' => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
            'data' => ['nullable', 'string'],
        ];
    }

    protected function search(): array
    {
        return ['id', 'title', 'crc32'];
    }
}
