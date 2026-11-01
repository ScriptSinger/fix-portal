<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Comment;
use App\Models\Firmware;
use App\Models\Post;
use App\Models\Question;
use App\MoonShine\Fields\LocalDate;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Support\Enums\Action;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\Link;
use MoonShine\UI\Fields\DateRange;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Textarea;

#[Icon('chat-bubble-left-right')]
#[Group('Обсуждения', 'chat-bubble-left-right')]
#[Order(1)]
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
            Preview::make('К чему', 'commentable_type', function (Comment $comment): string {
                $target = $comment->commentable;
                $label = class_basename((string) $comment->commentable_type).' #'.$comment->commentable_id;

                $url = match (true) {
                    $target instanceof Post => route('articles.show', ['article' => $target->slug]),
                    $target instanceof Question => route('questions.show', ['question' => $target->slug]),
                    $target instanceof Firmware => route('firmwares.show', ['firmware' => $target->getKey()]),
                    default => null,
                };

                return $url
                    ? Link::make($url, $label)->blank()->render()->render()
                    : $label;
            }),
            LocalDate::make('Создан', 'created_at')->format('d.m.Y H:i')->sortable(),
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

    protected function filters(): iterable
    {
        return [
            BelongsTo::make('Автор', 'user', 'name', UserResource::class)->nullable(),
            Select::make('Раздел', 'commentable_type')
                ->options([
                    Question::class => 'Вопросы',
                    Post::class => 'Статьи',
                    Firmware::class => 'Прошивки',
                ])
                ->nullable()
                ->onApply(static function (Builder $query, $value): Builder {
                    return blank($value) ? $query : $query->where('commentable_type', $value);
                }),
            DateRange::make('Создан', 'created_at'),
        ];
    }
}
