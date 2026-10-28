<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Enums\AnnouncementKind;
use App\Models\Question;
use App\Services\Announcements\Channels\VkChannel;
use App\Services\Announcements\QuestionAnnouncer;
use Illuminate\Http\Request;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Support\Attributes\AsyncMethod;
use MoonShine\Support\Enums\ToastType;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\ActionButton;

/**
 * Список вопросов; у ещё не отправленных в чаты есть кнопка «Отправить в чат». Так выборочно поднимают старые вопросы:
 * автоматически уходят только новые.
 */
class QuestionsIndexPage extends IndexPage
{
    protected function buttons(): ListOf
    {
        return parent::buttons()->add(
            ActionButton::make('Отправить в чат')
                ->icon('paper-airplane')
                ->canSee(fn (Question $question) => ! $question->announcements()->where('kind', AnnouncementKind::New)->exists())
                ->method('announce', events: [$this->getListEventName()])
                ->withConfirm('Отправить в чат?', $this->confirmText(), 'Отправить')
        );
    }

    #[AsyncMethod]
    public function announce(Request $request, QuestionAnnouncer $announcer): void
    {
        $question = Question::find($request->input('resourceItem'));

        if (! $question) {
            toast('Вопрос не найден', ToastType::ERROR);

            return;
        }

        $queued = $announcer->announce($question);

        if ($queued > 0) {
            toast('Вопрос поставлен в очередь на отправку, результат смотрите в «Рассылки → Публикации»', ToastType::SUCCESS);

            return;
        }

        toast('Вопрос уже отправлялся, или ни один канал не настроен', ToastType::ERROR);
    }

    private function confirmText(): string
    {
        $text = 'Вопрос уйдёт в настроенные чаты.';

        // ключ сообщества не умеет править и удалять записи на стене
        if (app(VkChannel::class)->enabled()) {
            $text .= ' Запись на стене VK потом нельзя изменить или удалить с сайта.';
        }

        return $text;
    }
}
