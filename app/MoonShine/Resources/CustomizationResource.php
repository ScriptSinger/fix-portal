<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Customization;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

#[Icon('paint-brush')]
#[Group('Настройки', 'cog-6-tooth')]
#[Order(12)]
/**
 * @extends ModelResource<Customization>
 */
class CustomizationResource extends BaseResource
{
    protected string $model = Customization::class;

    protected string $column = 'title';

    public function getTitle(): string
    {
        return 'Кастомизация сайта';
    }

    protected function indexFields(): iterable
    {
        return [
            ID::make(),
            Text::make('Заголовок', 'title'),
            Text::make('Copyright', 'copyright'),
        ];
    }

    protected function formFields(): iterable
    {
        return [
            ID::make(),
            Text::make('Заголовок', 'title')->required(),
            Textarea::make('Описание', 'description'),
            Text::make('Copyright', 'copyright'),
            Image::make('Favicon', 'favicon')->dir('customization')->allowedExtensions(['jpg', 'jpeg', 'png', 'gif', 'ico']),
            Image::make('Логотип', 'logo')->dir('customization')->allowedExtensions(['jpg', 'jpeg', 'png', 'gif']),
            Image::make('Баннер', 'banner')->dir('customization')->allowedExtensions(['jpg', 'jpeg', 'png', 'gif']),
        ];
    }

    protected function rules($item): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:300'],
            'copyright' => ['nullable', 'string', 'max:255'],
            'favicon' => ['nullable', 'file', 'max:2048'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'banner' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
