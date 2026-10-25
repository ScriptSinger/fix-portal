<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\MoonShine\Pages\BlocksIndexPage;
use App\MoonShine\Pages\ResourceFormPage;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use App\Models\Blocker;
use Illuminate\Validation\Rule;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

#[Icon('x-circle')]
#[Group('Безопасность', 'shield-check')]
#[Order(10)]
/**
 * @extends ModelResource<Blocker>
 */
class BlockerResource extends BaseResource
{
    protected string $model = Blocker::class;

    protected string $column = 'ip_address';

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
        return 'Блокировки';
    }

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('IP-адрес', 'ip_address')->sortable(),
            Date::make('Добавлен', 'created_at')->format('d.m.Y H:i')->sortable(),
        ];
    }

    protected function formFields(): iterable
    {
        return [
            ID::make(),
            Text::make('IP-адрес', 'ip_address')->required(),
        ];
    }

    protected function rules($item): array
    {
        return [
            'ip_address' => ['required', 'ip', Rule::unique('blockers', 'ip_address')->ignore($item?->getKey())],
        ];
    }

    protected function search(): array
    {
        return ['ip_address'];
    }
}
