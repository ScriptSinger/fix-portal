<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Question;
use App\MoonShine\Fields\LocalDate;
use App\MoonShine\Pages\QuestionsIndexPage;
use App\MoonShine\Pages\ResourceFormPage;
use App\MoonShine\Support\TinyMceUpload;
use Illuminate\Contracts\Database\Eloquent\Builder;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\TinyMce\Fields\TinyMce;
use MoonShine\UI\Fields\DateRange;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

#[Icon('question-mark-circle')]
#[Group('Вопросы', 'question-mark-circle')]
#[Order(1)]
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

    /**
     * @return list<class-string>
     */
    protected function pages(): array
    {
        return [
            QuestionsIndexPage::class,
            ResourceFormPage::class,
            DetailPage::class,
        ];
    }

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Заголовок', 'title')->sortable()->link(
                static fn (string $value, Text $field): string => route(
                    'questions.show',
                    ['question' => $field->getData()?->getOriginal()->slug],
                ),
                icon: 'arrow-top-right-on-square',
                blank: true,
            ),
            BelongsTo::make('Тип техники', 'appliance', 'title', ApplianceResource::class),
            BelongsTo::make('Автор', 'user', 'name', UserResource::class),
            Number::make('Просмотры', 'views')->sortable(),
            LocalDate::make('Создан', 'created_at')->format('d.m.Y H:i')->sortable(),
        ];
    }

    protected function detailFields(): iterable
    {
        return [
            ...$this->indexFields(),
            Textarea::make('Описание', 'description')->unescape(),
            HasMany::make('Комментарии и ответы', 'comments', resource: CommentResource::class),
            HasMany::make('Публикации', 'announcements', resource: QuestionAnnouncementResource::class),
        ];
    }

    protected function formFields(): iterable
    {
        return [
            ID::make(),
            Text::make('Заголовок', 'title')->required(),
            TinyMceUpload::withImageUpload(TinyMce::make('Описание', 'description'))
                ->required(),
            BelongsTo::make('Тип техники', 'appliance', 'title', ApplianceResource::class)
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

    protected function filters(): iterable
    {
        return [
            Select::make('Статус', 'content_status')
                ->options([
                    'solved' => 'Решённые',
                    'open' => 'Открытые',
                    'unanswered' => 'Без ответа',
                ])
                ->nullable()
                ->onApply(static function (Builder $query, $value): Builder {
                    return match ($value) {
                        'solved' => $query->whereNotNull('solved_at'),
                        'open' => $query->whereNull('solved_at'),
                        'unanswered' => $query->unanswered(),
                        default => $query,
                    };
                }),
            BelongsTo::make('Тип техники', 'appliance', 'title', ApplianceResource::class)->nullable(),
            BelongsTo::make('Автор', 'user', 'name', UserResource::class)->nullable(),
            DateRange::make('Создан', 'created_at'),
        ];
    }
}
