<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\TelegramQuestionMessage;
use App\MoonShine\Fields\LocalDate;
use App\MoonShine\Pages\TelegramQuestionMessagesIndexPage;
use App\MoonShine\Pages\ResourceFormPage;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Support\Attributes\Icon;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Enums\Action;
use MoonShine\Support\ListOf;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

#[Icon('inbox-arrow-down')]
#[Group('Вопросы', 'question-mark-circle')]
#[Order(2)]
/** @extends ModelResource<TelegramQuestionMessage> */
class TelegramQuestionMessageResource extends BaseResource
{
    protected string $model = TelegramQuestionMessage::class;

    protected string $column = 'id';

    protected array $with = ['appliance', 'question'];

    protected bool $simplePaginate = true;

    public function getTitle(): string
    {
        return 'Telegram-вопросы';
    }

    protected function activeActions(): ListOf
    {
        return parent::activeActions()->only(Action::VIEW, Action::UPDATE);
    }

    protected function pages(): array
    {
        return [TelegramQuestionMessagesIndexPage::class, ResourceFormPage::class, DetailPage::class];
    }

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Текст', 'text')->prettyLimit(limit: 120)->sortable(),
            Enum::make('Статус', 'status')->options([
                'pending_moderation' => 'Ожидает модерации',
                'published' => 'Опубликован',
                'rejected' => 'Отклонён',
                'failed' => 'Ошибка',
            ]),
            BelongsTo::make('Тип техники', 'appliance', 'title', ApplianceResource::class)->nullable(),
            Number::make('Telegram message ID', 'telegram_message_id'),
            LocalDate::make('Получен', 'created_at')->format('d.m.Y H:i')->sortable(),
        ];
    }

    protected function detailFields(): iterable
    {
        return [
            ...$this->indexFields(),
            Preview::make('Фотографии', 'payload', function (TelegramQuestionMessage $item): string {
                $photos = $item->telegramPhotoFileIds();

                if ($photos === []) {
                    return '<span class="text-gray-500">Нет прикреплённых фотографий</span>';
                }

                return '<div style="display:flex;flex-wrap:wrap;gap:12px;max-width:900px">'.collect($photos)
                    ->values()
                    ->map(fn (string $fileId, int $index): string => '<a href="'.e(route('moonshine.telegram-question-photo', [$item->id, $index])).'" target="_blank" rel="noopener"><img src="'.e(route('moonshine.telegram-question-photo', [$item->id, $index])).'" alt="Фото '.($index + 1).'" style="width:180px;height:180px;object-fit:cover;border-radius:8px;border:1px solid #ddd"></a>')
                    ->implode('').'</div>';
            }),
            Textarea::make('Причина отказа', 'error_message')->nullable(),
            Text::make('Question ID', 'question_id'),
        ];
    }

    protected function formFields(): iterable
    {
        return [
            ID::make(),
            BelongsTo::make('Тип техники', 'appliance', 'title', ApplianceResource::class)
                ->searchable()
                ->nullable(),
            Textarea::make('Причина отказа', 'error_message')->nullable(),
        ];
    }

    protected function rules($item): array
    {
        return [
            'appliance_id' => ['nullable', 'exists:appliances,id'],
            'error_message' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
