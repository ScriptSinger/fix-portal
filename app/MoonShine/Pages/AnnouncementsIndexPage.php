<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Enums\AnnouncementStatus;
use App\Models\QuestionAnnouncement;
use App\Services\Announcements\QuestionAnnouncer;
use Illuminate\Http\Request;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Support\Attributes\AsyncMethod;
use MoonShine\Support\Enums\ToastType;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\ActionButton;

/** Список отправок вопросов в чаты; у неудавшихся есть кнопка «Повторить». */
class AnnouncementsIndexPage extends IndexPage
{
    protected function buttons(): ListOf
    {
        return parent::buttons()->add(
            ActionButton::make('Повторить')
                ->icon('arrow-path')
                ->canSee(fn (QuestionAnnouncement $announcement) => $announcement->status === AnnouncementStatus::Failed)
                ->method('retry', events: [$this->getListEventName()])
        );
    }

    #[AsyncMethod]
    public function retry(Request $request, QuestionAnnouncer $announcer): void
    {
        $announcement = QuestionAnnouncement::find($request->input('resourceItem'));

        if ($announcement && $announcer->retry($announcement)) {
            toast('Отправка поставлена в очередь', ToastType::SUCCESS);

            return;
        }

        toast('Повторить можно только неудавшуюся отправку', ToastType::ERROR);
    }
}
