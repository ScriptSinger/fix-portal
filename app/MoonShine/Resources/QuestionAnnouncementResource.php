<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Enums\AnnouncementStatus;
use App\Models\QuestionAnnouncement;
use App\MoonShine\Pages\AnnouncementsIndexPage;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Support\Enums\Action;
use MoonShine\Support\ListOf;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

#[Icon('paper-airplane')]
#[Group('Контент', 'document-text')]
#[Order(5)]
/**
 * @extends ModelResource<QuestionAnnouncement>
 */
class QuestionAnnouncementResource extends BaseResource
{
    protected string $model = QuestionAnnouncement::class;

    protected string $column = 'id';

    protected array $with = ['question'];

    protected bool $simplePaginate = true;

    protected string $sortColumn = 'id';

    public function getTitle(): string
    {
        return 'Публикации вопросов';
    }

    /** Записи создаёт сайт при публикации вопроса: здесь их только смотрят, а неудавшиеся отправляют заново. */
    protected function activeActions(): ListOf
    {
        return parent::activeActions()->only(Action::VIEW);
    }

    /**
     * @return list<class-string>
     */
    protected function pages(): array
    {
        return [
            AnnouncementsIndexPage::class,
            DetailPage::class,
        ];
    }

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Вопрос', 'question', 'title', QuestionResource::class),
            Text::make('Канал', 'channel'),
            Enum::make('Статус', 'status')->attach(AnnouncementStatus::class),
            Number::make('Попытки', 'attempts'),
            Date::make('Отправлено', 'sent_at')->format('d.m.Y H:i')->sortable(),
            Date::make('Создано', 'created_at')->format('d.m.Y H:i')->sortable(),
        ];
    }

    protected function detailFields(): iterable
    {
        return [
            ...$this->indexFields(),
            Text::make('Номер сообщения', 'external_id'),
            Textarea::make('Последняя ошибка', 'last_error'),
        ];
    }

    protected function filters(): iterable
    {
        return [
            Enum::make('Статус', 'status')->attach(AnnouncementStatus::class)->nullable(),
        ];
    }

    protected function search(): array
    {
        return ['id', 'question.title'];
    }
}
