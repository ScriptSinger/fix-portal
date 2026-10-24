<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\MoonShine\Resources\BaseResource;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Laravel\Pages\Crud\FormPage;

/**
 * Общая страница формы: правила валидации берутся из rules() ресурса,
 * чтобы не заводить отдельный класс страницы под каждый ресурс.
 */
class ResourceFormPage extends FormPage
{
    protected function rules(DataWrapperContract $item): array
    {
        $resource = $this->getResource();

        return $resource instanceof BaseResource
            ? $resource->validationRules($item)
            : [];
    }
}
