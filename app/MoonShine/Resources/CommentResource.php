<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Comment;
use Illuminate\Support\Str;
use MoonShine\Support\Enums\Action;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Support\ListOf;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Textarea;

#[Icon('chat-bubble-left-right')]
#[Group('Контент', 'document-text')]
#[Order(6)]
/**
 * @extends ModelResource<Comment>
 */
class CommentResource extends BaseResource
{
    protected string $model = Comment::class;

    protected string $column = 'text';

    protected array $with = ['user', 'commentable'];

    protected bool $simplePaginate = true;

    protected string $sortColumn = 'id';

    public function getTitle(): string
    {
        return 'Комментарии и ответы';
    }

    protected function activeActions(): ListOf
    {
        return parent::activeActions()->except(Action::CREATE);
    }

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Preview::make('Текст', 'text', fn (Comment $c) => Str::limit(strip_tags((string) $c->text), 100)),
            BelongsTo::make('Автор', 'user', 'name', UserResource::class),
            Preview::make('К чему', 'commentable_type', fn (Comment $c) => class_basename((string) $c->commentable_type)
                . ' #' . $c->commentable_id),
            Date::make('Создан', 'created_at')->format('d.m.Y H:i')->sortable(),
        ];
    }

    protected function detailFields(): iterable
    {
        return [
            ...$this->indexFields(),
            Textarea::make('Полный текст', 'text'),
            // Ответы показываются внутри комментария, отдельного пункта меню для них нет
            HasMany::make('Ответы', 'replies', resource: ReplyResource::class),
        ];
    }

    protected function formFields(): iterable
    {
        return [
            ID::make(),
            Textarea::make('Текст', 'text')->required(),
        ];
    }

    protected function rules($item): array
    {
        return [
            'text' => ['required', 'string'],
        ];
    }

    protected function search(): array
    {
        return ['id', 'text'];
    }
}
