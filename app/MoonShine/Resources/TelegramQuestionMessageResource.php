<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\TelegramQuestionMessage;
use App\Enums\TelegramQuestionConfirmationStatus;
use App\MoonShine\Fields\LocalDate;
use App\MoonShine\Fields\TelegramQuestionPhotos;
use App\MoonShine\Pages\TelegramQuestionMessagesIndexPage;
use App\MoonShine\Pages\ResourceFormPage;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
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
        return 'Публикация Telegram-вопросов на сайте';
    }

    protected function modifyQueryBuilder(Builder $builder): Builder
    {
        return $builder->whereHas('publication');
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

    public function delete(DataWrapperContract $item, ?FieldsContract $fields = null): bool
    {
        $message = $item->getOriginal();
        if (! $message instanceof TelegramQuestionMessage) {
            return false;
        }

        return DB::transaction(function () use ($message): bool {
            $record = DB::table('telegram_question_messages')->where('id', $message->getKey())->lockForUpdate()->first();
            $publication = DB::table('telegram_question_publications')->where('telegram_question_message_id', $message->getKey())->lockForUpdate()->first();
            if (! $record || ! $publication || $publication->status === 'published') {
                return false;
            }
            DB::table('telegram_question_publications')->where('id', $publication->id)->delete();
            DB::table('telegram_question_messages')->where('id', $record->id)->update([
                'status' => $record->registered_at !== null ? 'registered'
                    : ($record->community_confirmation_status === TelegramQuestionConfirmationStatus::Pending->value ? 'awaiting_confirmation' : 'cancelled'),
                'question_id' => null, 'appliance_id' => null, 'error_message' => null,
                'moderation_chat_id' => null, 'moderation_message_id' => null, 'updated_at' => now(),
            ]);

            return true;
        });
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
            TelegramQuestionPhotos::make(),
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
