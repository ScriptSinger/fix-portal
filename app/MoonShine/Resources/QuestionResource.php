<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Question;
use App\MoonShine\Support\TinyMceUpload;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\TinyMce\Fields\TinyMce;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

#[Icon('question-mark-circle')]
#[Group('Контент', 'document-text')]
#[Order(4)]
/**
 * @extends ModelResource<Question>
 */
class QuestionResource extends BaseResource
{
    protected string $model = Question::class;

    protected string $column = 'title';

    protected array $with = ['appliance', 'user'];

    protected bool $simplePaginate = true;

    protected string $sortColumn = 'id';

    public function getTitle(): string
    {
        return 'Вопросы';
    }

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Заголовок', 'title')->sortable(),
            BelongsTo::make('Техника', 'appliance', 'title', ApplianceResource::class),
            BelongsTo::make('Автор', 'user', 'name', UserResource::class),
            Number::make('Просмотры', 'views')->sortable(),
            Date::make('Создан', 'created_at')->format('d.m.Y H:i')->sortable(),
        ];
    }

    protected function detailFields(): iterable
    {
        return [
            ...$this->indexFields(),
            Textarea::make('Описание', 'description')->unescape(),
        ];
    }

    protected function formFields(): iterable
    {
        return [
            ID::make(),
            Text::make('Заголовок', 'title')->required(),
            TinyMceUpload::withImageUpload(TinyMce::make('Описание', 'description'))
                ->required(),
            BelongsTo::make('Техника', 'appliance', 'title', ApplianceResource::class)
                ->searchable()
                ->required(),
            BelongsTo::make('Автор', 'user', 'name', UserResource::class)
                ->asyncSearch('name')
                ->required(),
        ];
    }

    protected function rules($item): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'appliance_id' => ['required', 'exists:appliances,id'],
            'user_id' => ['required', 'exists:users,id'],
        ];
    }

    protected function search(): array
    {
        return ['id', 'title'];
    }
}
