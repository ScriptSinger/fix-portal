<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use MoonShine\ColorManager\ColorManager;
use MoonShine\Contracts\ColorManager\ColorManagerContract;
use MoonShine\Laravel\Layouts\AppLayout;
use MoonShine\Laravel\Resources\MoonShineUserResource;
use MoonShine\MenuManager\MenuGroup;
use MoonShine\MenuManager\MenuItem;
use MoonShine\UI\Components\Layout\Layout;

final class MoonShineLayout extends AppLayout
{
    protected function assets(): array
    {
        return [
            ...parent::assets(),
        ];
    }

    protected function menu(): array
    {
        // Ресурсы пакета («Администраторы» и «Роли») MoonShine сам кладёт в группу «Система» и ставит её первой.
        // Убираем её: роль у нас одна, отдельный раздел не нужен, а администраторы идут последним пунктом.
        $groups = collect($this->autoloadMenu())->reject(
            fn ($element) => $element instanceof MenuGroup
                && method_exists($element, 'getLabel')
                && $element->getLabel() === __('moonshine::ui.resource.system')
        );

        return [
            ...$groups->values()->all(),
            MenuItem::make(MoonShineUserResource::class, 'Администраторы')->icon('users'),
        ];
    }

    /**
     * @param  ColorManager  $colorManager
     */
    protected function colors(ColorManagerContract $colorManager): void
    {
        parent::colors($colorManager);

        // $colorManager->primary('#00000');
    }

    public function build(): Layout
    {
        return parent::build();
    }
}
