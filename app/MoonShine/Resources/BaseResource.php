<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\MoonShine\Pages\ResourceFormPage;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @template TModel of Model
 * @extends ModelResource<TModel>
 */
abstract class BaseResource extends ModelResource
{
    /**
     * @return list<class-string>
     */
    protected function pages(): array
    {
        return [
            IndexPage::class,
            ResourceFormPage::class,
            DetailPage::class,
        ];
    }

    /**
     * Правила валидации формы. Модель приходит уже приведённой из DataWrapper.
     *
     * @return array<string, mixed>
     */
    protected function rules(mixed $item): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public function validationRules(DataWrapperContract $item): array
    {
        return $this->rules($item->getOriginal());
    }
}
