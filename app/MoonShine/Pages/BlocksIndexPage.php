<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\MoonShine\Resources\BlockedUserAgentResource;
use App\MoonShine\Resources\BlockerResource;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Layout\Flex;

/**
 * Список блокировок. В меню один пункт «Блокировки», а между IP-адресами и User-Agent
 * переключаются кнопками над таблицей.
 */
class BlocksIndexPage extends IndexPage
{
    protected function topLayer(): array
    {
        $ip = app(BlockerResource::class);
        $agents = app(BlockedUserAgentResource::class);
        $onIp = $this->getResource() instanceof BlockerResource;

        $ipButton = ActionButton::make('IP-адреса', $ip->getIndexPageUrl())->icon('x-circle');
        $agentButton = ActionButton::make('User-Agent', $agents->getIndexPageUrl())->icon('bug-ant');

        // Активная вкладка выделена цветом
        ($onIp ? $ipButton : $agentButton)->primary();

        return [
            Flex::make([$ipButton, $agentButton])->justifyAlign('start')->customAttributes(['class' => 'mb-4']),
            ...parent::topLayer(),
        ];
    }
}
