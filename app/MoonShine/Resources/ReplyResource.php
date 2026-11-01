<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Reply;
use App\MoonShine\Fields\LocalDate;
use Illuminate\Support\Str;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\MenuManager\Attributes\SkipMenu;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Support\Enums\Action;
use MoonShine\Support\ListOf;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Textarea;

#[Icon('arrow-uturn-left')]
#[Group('Обсуждения', 'chat-bubble-left-right')]
#[SkipMenu]
#[Order(2)]
/**
 * @extends ModelResource<Reply>
 */
class ReplyResource extends BaseResource
{
    protected string $model = Reply::class;

    protected string $column = 'text';

    protected array $with = ['user'];

    protected bool $simplePaginate = true;

    protected string $sortColumn = 'id';

    public function getTitle(): string
    {
        return 'Ответы';
    }

    protected function activeActions(): ListOf
    {
        return parent::activeActions()->except(Action::CREATE);
    }

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Preview::make('Текст', 'text', fn (Reply $r) => Str::limit(strip_tags((string) $r->text), 100)),
            BelongsTo::make('Автор', 'user', 'name', UserResource::class),
            Preview::make('Комментарий', 'comment_id', fn (Reply $r) => '#'.$r->comment_id),
            LocalDate::make('Создан', 'created_at')->format('d.m.Y H:i')->sortable(),
        ];
    }

    protected function detailFields(): iterable
    {
        return [
            ...$this->indexFields(),
            Textarea::make('Полный текст', 'text'),
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
