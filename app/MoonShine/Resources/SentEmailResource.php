<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\SentEmail;
use App\MoonShine\Fields\LocalDate;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Support\Enums\Action;
use MoonShine\Support\ListOf;
use MoonShine\UI\Fields\Preview;

#[Icon('envelope')]
#[Group('Рассылки', 'megaphone')]
#[Order(2)]
/**
 * @extends ModelResource<SentEmail>
 */
class SentEmailResource extends BaseResource
{
    protected string $model = SentEmail::class;

    protected string $column = 'id';

    protected array $with = ['user'];

    protected bool $simplePaginate = true;

    protected string $sortColumn = 'emailed_at';

    public function getTitle(): string
    {
        return 'Письма';
    }

    /** Письма уходят с сайта сами: здесь их только смотрят. */
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
            IndexPage::class,
            DetailPage::class,
        ];
    }

    protected function indexFields(): iterable
    {
        return [
            LocalDate::make('Отправлено', 'emailed_at')->format('d.m.Y H:i')->sortable(),
            BelongsTo::make('Кому', 'user', 'name', UserResource::class),
            Preview::make('Что', 'data', fn (SentEmail $mail) => $mail->kindLabel()),
            Preview::make('Вопрос', 'data', fn (SentEmail $mail) => (string) ($mail->data['title'] ?? '')),
            LocalDate::make('Прочитано на сайте', 'read_at')->format('d.m.Y H:i'),
        ];
    }

    protected function detailFields(): iterable
    {
        return [
            ...$this->indexFields(),
            Preview::make('Текст ответа', 'data', fn (SentEmail $mail) => (string) ($mail->data['snippet'] ?? '')),
            Preview::make('Ссылка', 'data', fn (SentEmail $mail) => (string) ($mail->data['url'] ?? '')),
        ];
    }
}
