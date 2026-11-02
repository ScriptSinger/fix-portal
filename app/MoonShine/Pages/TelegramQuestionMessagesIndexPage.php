<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Models\TelegramQuestionMessage;
use App\Services\TelegramQuestionModerationService;
use Illuminate\Http\Request;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Support\Attributes\AsyncMethod;
use MoonShine\Support\Enums\ToastType;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\ActionButton;

class TelegramQuestionMessagesIndexPage extends IndexPage
{
    protected function buttons(): ListOf
    {
        return parent::buttons()
            ->add(ActionButton::make('Опубликовать')
                ->icon('check')
                ->canSee(fn (TelegramQuestionMessage $item): bool => $item->status === 'pending_moderation' && $item->appliance_id !== null)
                ->method('approve', events: [$this->getListEventName()])
                ->withConfirm('Опубликовать вопрос?', 'Вопрос будет создан на сайте.', 'Опубликовать'))
            ->add(ActionButton::make('Отклонить')
                ->icon('x-mark')
                ->canSee(fn (TelegramQuestionMessage $item): bool => $item->status === 'pending_moderation')
                ->method('reject', events: [$this->getListEventName()])
                ->withConfirm('Отклонить вопрос?', 'Причина берётся из поля «Причина отказа».', 'Отклонить'))
            ->add(ActionButton::make('Повторить preview')
                ->icon('arrow-path')
                ->canSee(fn (TelegramQuestionMessage $item): bool => $item->status === 'pending_moderation')
                ->method('retry', events: [$this->getListEventName()]))
            ->add(ActionButton::make('Удалить из очереди')
                ->icon('trash')
                ->canSee(fn (TelegramQuestionMessage $item): bool => true)
                ->method('remove', events: [$this->getListEventName()])
                ->withConfirm('Убрать запись из модерации?', 'Question на сайте и публикация в Telegram не удаляются.', 'Убрать'));
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

    #[AsyncMethod]
    public function remove(Request $request): void
    {
        $item = TelegramQuestionMessage::find($request->integer('resourceItem'));
        if (! $item) {
            toast('Запись не найдена', ToastType::ERROR);

            return;
        }
        $item->delete();
        toast('Запись убрана из модерации. Опубликованный вопрос сохранён.', ToastType::SUCCESS);
    }

    private function toast(string $message, bool $success = false): void
    {
        toast($message, $success ? ToastType::SUCCESS : ToastType::ERROR);
    }
}
