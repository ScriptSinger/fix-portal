<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Enums\TelegramQuestionResolutionStatus;
use App\Models\TelegramQuestionMessage;
use App\MoonShine\Fields\LocalDate;
use Illuminate\Contracts\Database\Eloquent\Builder;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Support\Enums\Action;
use MoonShine\Support\ListOf;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

#[Icon('chat-bubble-left-right')]
#[Group('Вопросы', 'question-mark-circle')]
#[Order(2)]
/** @extends ModelResource<TelegramQuestionMessage> */
class TelegramCommunityQuestionResource extends BaseResource
{
    protected string $model = TelegramQuestionMessage::class;

    protected string $column = 'id';

    protected bool $simplePaginate = true;

    protected string $sortColumn = 'id';

    public function getTitle(): string
    {
        return 'Вопросы Telegram-группы';
    }

    protected function modifyQueryBuilder(Builder $builder): Builder
    {
        return $builder->whereNotNull('registered_at')->whereNull('archived_at');
    }

    protected function activeActions(): ListOf
    {
        return parent::activeActions()->only(Action::VIEW);
    }

    protected function pages(): array
    {
        return [IndexPage::class, DetailPage::class];
    }

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Вопрос', 'text')->prettyLimit(limit: 120),
            Preview::make('Автор', 'telegram_user_id', fn (TelegramQuestionMessage $item): string => $this->participant($item, false)),
            Enum::make('Решение', 'resolution_status')->attach(TelegramQuestionResolutionStatus::class),
            Preview::make('Помог', 'solution_telegram_user_id', fn (TelegramQuestionMessage $item): string => $item->solution_telegram_user_id
                ? $this->participant($item, true) : '—'),
            LocalDate::make('Зарегистрирован', 'registered_at')->format('d.m.Y H:i')->sortable(),
        ];
    }

    protected function detailFields(): iterable
    {
        return [
            ID::make(),
            Textarea::make('Вопрос', 'text')->readonly(),
            Preview::make('Автор', 'telegram_user_id', fn (TelegramQuestionMessage $item): string => $this->participant($item, false)),
            Enum::make('Решение', 'resolution_status')->attach(TelegramQuestionResolutionStatus::class),
            Preview::make('Помог', 'solution_telegram_user_id', fn (TelegramQuestionMessage $item): string => $item->solution_telegram_user_id
                ? $this->participant($item, true) : '—'),
            Number::make('ID сообщения с решением', 'solution_telegram_message_id'),
            LocalDate::make('Решён', 'resolved_at')->format('d.m.Y H:i'),
            LocalDate::make('Зарегистрирован', 'registered_at')->format('d.m.Y H:i'),
            Number::make('ID вопроса на сайте', 'question_id'),
        ];
    }

    protected function filters(): iterable
    {
        return [
            Enum::make('Решение', 'resolution_status')->attach(TelegramQuestionResolutionStatus::class)->nullable(),
        ];
    }

    private function participant(TelegramQuestionMessage $item, bool $helper): string
    {
        $id = (int) ($helper ? $item->solution_telegram_user_id : $item->telegram_user_id);
        $name = $helper ? '' : trim(implode(' ', array_filter([
            (string) data_get($item->payload, 'message.from.first_name', ''),
            (string) data_get($item->payload, 'message.from.last_name', ''),
        ])));
        $label = $name !== '' ? e($name) : 'Telegram ID '.$id;

        return $id > 0 ? '<a href="tg://user?id='.$id.'">'.$label.'</a>' : '—';
    }
}
