<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\MoonShine\Pages\BlocksIndexPage;
use App\MoonShine\Pages\ResourceFormPage;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use App\Models\BlockedUserAgent;
use Illuminate\Validation\Rule;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\MenuManager\Attributes\SkipMenu;
use MoonShine\Support\Attributes\Icon;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

#[Icon('bug-ant')]
#[Group('Безопасность', 'shield-check')]
#[SkipMenu]
#[Order(11)]
/**
 * @extends ModelResource<BlockedUserAgent>
 */
class BlockedUserAgentResource extends BaseResource
{
    protected string $model = BlockedUserAgent::class;

    protected string $column = 'user_agent';

    protected bool $simplePaginate = true;

    /**
     * @return list<class-string>
     */
    protected function pages(): array
    {
        return [
            BlocksIndexPage::class,
            ResourceFormPage::class,
            DetailPage::class,
        ];
    }

    protected string $sortColumn = 'id';

    public function getTitle(): string
    {
        return 'Заблокированные User-Agent';
    }

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('User-Agent', 'user_agent')->sortable(),
            Date::make('Добавлен', 'created_at')->format('d.m.Y H:i')->sortable(),
        ];
    }

    protected function formFields(): iterable
    {
        return [
            ID::make(),
            Text::make('User-Agent', 'user_agent')->required(),
        ];
    }

    protected function rules($item): array
    {
        return [
            'user_agent' => ['required', 'string', 'max:255', Rule::unique('blocked_user_agents', 'user_agent')->ignore($item?->getKey())],
        ];
    }

    protected function search(): array
    {
        return ['user_agent'];
    }
}
