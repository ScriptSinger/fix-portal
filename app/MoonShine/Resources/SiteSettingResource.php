<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\SiteSetting;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Support\Enums\Action;
use MoonShine\Support\ListOf;
use MoonShine\UI\Fields\Switcher;

#[Icon('megaphone')]
#[Group('Настройки', 'cog-6-tooth')]
#[Order(12)]
/**
 * @extends ModelResource<SiteSetting>
 */
class SiteSettingResource extends BaseResource
{
    protected string $model = SiteSetting::class;

    protected string $column = 'id';

    public function getTitle(): string
    {
        return 'Реклама';
    }

    /**
     * Строка настроек одна и создаётся миграцией: только менять, без создания и удаления.
     */
    protected function activeActions(): ListOf
    {
        return parent::activeActions()->only(Action::UPDATE);
    }

    protected function indexFields(): iterable
    {
        return [
            Switcher::make('Показывать рекламу РСЯ', 'ads_enabled')->updateOnPreview(),
        ];
    }

    protected function formFields(): iterable
    {
        return [
            Switcher::make('Показывать рекламу РСЯ', 'ads_enabled')
                ->hint('Включайте после того, как у площадки появится действующий договор в Рекламной сети Яндекса'),
        ];
    }

    protected function rules($item): array
    {
        return [
            'ads_enabled' => ['boolean'],
        ];
    }
}
