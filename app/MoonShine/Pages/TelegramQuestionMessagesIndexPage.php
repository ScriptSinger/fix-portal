<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Models\TelegramQuestionMessage;
use App\Services\TelegramQuestionModerationService;
use Illuminate\Http\Request;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Support\Attributes\AsyncMethod;
use MoonShine\Support\Enums\ToastType;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\ActionButton;

class TelegramQuestionMessagesIndexPage extends IndexPage
{
    protected function buttons(): ListOf
    {
        return new ListOf(ActionButtonContract::class, [
            ActionButton::make(
                '',
                url: fn (mixed $item): string => $this->getResource()->getDetailPageUrl((int) $item->getKey()),
            )
                ->icon('eye')
                ->square()
                ->showInLine(),
            $this->getResource()->getEditButton(isAsync: $this->isAsync()),
            ActionButton::make('Опубликовать')
                ->icon('check')
                ->customAttributes(['title' => 'Опубликовать', 'aria-label' => 'Опубликовать'])
                ->showInDropdown()
                ->canSee(fn (TelegramQuestionMessage $item): bool => in_array($item->status, ['pending_moderation', 'failed'], true) && $item->appliance_id !== null)
                ->method('approve', events: [$this->getListEventName()])
                ->withConfirm('Опубликовать вопрос?', 'Вопрос будет создан на сайте.', 'Опубликовать'),
            ActionButton::make('Отклонить')
                ->icon('x-mark')
                ->customAttributes(['title' => 'Отклонить', 'aria-label' => 'Отклонить'])
                ->showInDropdown()
                ->canSee(fn (TelegramQuestionMessage $item): bool => $item->status === 'pending_moderation')
                ->method('reject', events: [$this->getListEventName()])
                ->withConfirm('Отклонить вопрос?', 'Причина берётся из поля «Причина отказа».', 'Отклонить'),
            ActionButton::make('Повторить')
                ->icon('arrow-path')
                ->customAttributes(['title' => 'Повторить preview', 'aria-label' => 'Повторить preview'])
                ->showInDropdown()
                ->canSee(fn (TelegramQuestionMessage $item): bool => $item->status === 'pending_moderation')
                ->method('retry', events: [$this->getListEventName()]),
            $this->getResource()->getDeleteButton(isAsync: $this->isAsync())
                ->canSee(fn (TelegramQuestionMessage $item): bool => $item->registered_at === null && $item->status !== 'published'),
            $this->getResource()->getMassDeleteButton(isAsync: $this->isAsync()),
        ]);
    }

    #[AsyncMethod]
    public function approve(Request $request, TelegramQuestionModerationService $service): void
    {
        $item = TelegramQuestionMessage::find($request->integer('resourceItem'));
        if (! $item || ! $item->appliance_id) {
            toast('Сначала выберите тип техники', ToastType::ERROR);

            return;
        }

        $this->toast($service->approve($item->telegram_message_id, $item->appliance_id, null), true);
    }

    #[AsyncMethod]
    public function reject(Request $request, TelegramQuestionModerationService $service): void
    {
        $item = TelegramQuestionMessage::find($request->integer('resourceItem'));
        if (! $item) {
            toast('Вопрос не найден', ToastType::ERROR);

            return;
        }

        $this->toast($service->reject($item->telegram_message_id, (string) $item->error_message, null), true);
    }

    #[AsyncMethod]
    public function retry(Request $request, TelegramQuestionModerationService $service): void
    {
        $item = TelegramQuestionMessage::find($request->integer('resourceItem'));
        if (! $item) {
            toast('Вопрос не найден', ToastType::ERROR);

            return;
        }

        $this->toast($service->retry($item->telegram_message_id, null), true);
    }

    private function toast(string $message, bool $success = false): void
    {
        toast($message, $success ? ToastType::SUCCESS : ToastType::ERROR);
    }
}
