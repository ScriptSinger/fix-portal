<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\TelegramQuestionMessage;
use App\MoonShine\Fields\LocalDate;
use App\MoonShine\Pages\TelegramQuestionMessagesIndexPage;
use App\MoonShine\Pages\ResourceFormPage;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Crud\Resources\CrudResource;
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
use MoonShine\UI\Components\ActionButton;

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
        return parent::activeActions()->only(
            Action::VIEW,
            Action::UPDATE,
            Action::DELETE,
            Action::MASS_DELETE,
        );
    }

    /**
     * The moderation queue may be cleaned up, but a published moderation
     * record must not be removed from the CRUD accidentally or via a direct
     * mass-delete request.
     */
    public function delete(DataWrapperContract $item, ?FieldsContract $fields = null): bool
    {
        if ($item->getOriginal() instanceof TelegramQuestionMessage
            && $item->getOriginal()->status === 'published') {
            return false;
        }

        return parent::delete($item, $fields);
    }

    /**
     * The index page provides its own compact eye action. Disable MoonShine's
     * default detail action to prevent a clipped "Показать" slot in the row.
     */
    public function getDetailButton(
        ?CrudResource $resource = null,
        ?string $modalName = null,
        bool $isSeparateModal = true,
    ): ActionButtonContract {
        return ActionButton::emptyHidden();
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
            Preview::make('Автор', 'telegram_user_id', fn (TelegramQuestionMessage $item): string => self::authorPreview($item)),
            Enum::make('Статус', 'status')->options([
                'pending_moderation' => 'Ожидает модерации',
                'published' => 'Опубликован',
                'rejected' => 'Отклонён',
                'failed' => 'Ошибка',
                'needs_context' => 'Нужно описание',
            ]),
            BelongsTo::make('Тип техники', 'appliance', 'title', ApplianceResource::class)->nullable(),
            Number::make('Telegram message ID', 'telegram_message_id'),
            LocalDate::make('Получен', 'created_at')->format('d.m.Y H:i')->sortable(),
        ];
    }

    protected function detailFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Preview::make('Текст вопроса', 'text', function (TelegramQuestionMessage $item): string {
                return '<div style="white-space:pre-wrap;line-height:1.55;max-width:900px">'.e((string) $item->text).'</div>';
            }),
            Preview::make('Автор', 'telegram_user_id', fn (TelegramQuestionMessage $item): string => self::authorPreview($item)),
            Enum::make('Статус', 'status')->options([
                'pending_moderation' => 'Ожидает модерации',
                'published' => 'Опубликован',
                'rejected' => 'Отклонён',
                'failed' => 'Ошибка',
                'needs_context' => 'Нужно описание',
            ]),
            BelongsTo::make('Тип техники', 'appliance', 'title', ApplianceResource::class)->nullable(),
            Number::make('Telegram message ID', 'telegram_message_id'),
            LocalDate::make('Получен', 'created_at')->format('d.m.Y H:i'),
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

    private static function authorPreview(TelegramQuestionMessage $item): string
    {
        $from = (array) data_get($item->payload, 'message.from', []);
        $name = trim(implode(' ', array_filter([
            (string) ($from['first_name'] ?? ''),
            (string) ($from['last_name'] ?? ''),
        ])));
        $username = trim((string) ($from['username'] ?? ''));
        $telegramId = (int) $item->telegram_user_id;
        $displayName = $name !== '' ? $name : ($username !== '' ? '@'.$username : 'Неизвестный автор');
        $usernameHtml = $username !== ''
            ? ' <a href="https://t.me/'.rawurlencode($username).'" target="_blank" rel="noopener">@'.e($username).'</a>'
            : '';

        return '<div><strong>'.e($displayName).'</strong>'.$usernameHtml
            .'<div style="font-size:.8em;color:#6b7280">Telegram ID: '.e((string) $telegramId).'</div></div>';
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
