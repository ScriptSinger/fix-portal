<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Enums\AnnouncementStatus;
use App\Enums\PostStatus;
use App\Models\Comment;
use App\Models\Firmware;
use App\Models\Post;
use App\Models\Question;
use App\Models\QuestionAnnouncement;
use App\Models\SentEmail;
use App\Models\User;
use App\MoonShine\Resources\QuestionAnnouncementResource;
use App\MoonShine\Resources\SentEmailResource;
use App\MoonShine\Resources\UserResource;
use App\MoonShine\Resources\VisitorResource;
use App\Services\DashboardMetrics;
use Illuminate\Support\Carbon;
use MoonShine\Apexcharts\Components\DonutChartMetric;
use MoonShine\Apexcharts\Components\LineChartMetric;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\Pages\Page;
use MoonShine\MenuManager\Attributes\SkipMenu;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Metrics\Wrapped\ValueMetric;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;

#[SkipMenu]
class Dashboard extends Page
{
    /**
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            '#' => $this->getTitle(),
        ];
    }

    public function getTitle(): string
    {
        return $this->title ?: 'Статистика';
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
    {
        return [
            Tabs::make([
                Tab::make('Обзор', [
                    Box::make('Контент', [
                        Grid::make([
                            $this->count('Статьи', Post::class, 'newspaper'),
                            $this->count('Вопросы', Question::class, 'question-mark-circle'),
                            $this->count('Прошивки', Firmware::class, 'cpu-chip'),
                        ]),
                    ]),
                    Box::make('Модерация', [
                        Grid::make([
                            $this->postStatus('Черновики статей', PostStatus::Draft, 'pencil-square'),
                            $this->unansweredQuestions(),
                            $this->recentComments(),
                        ]),
                    ]),
                    Box::make('Пользователи', [
                        Grid::make([
                            $this->linkedMetric(
                                $this->users(),
                                app(UserResource::class)->getIndexPageUrl(['query-tag' => 'verified']),
                                'Открыть подтверждённых пользователей',
                            ),
                            $this->linkedMetric(
                                $this->seen('Онлайн сейчас (за '.User::ONLINE_MINUTES.' мин)', 'signal', now()->subMinutes(User::ONLINE_MINUTES)),
                                app(VisitorResource::class)->getIndexPageUrl(['query-tag' => VisitorResource::TAG_ONLINE]),
                                'Открыть пользователей онлайн',
                            ),
                            $this->linkedMetric(
                                $this->seen('Заходили сегодня', 'sun', VisitorResource::startOfToday()),
                                app(VisitorResource::class)->getIndexPageUrl(['query-tag' => VisitorResource::TAG_TODAY]),
                                'Открыть посещения за сегодня',
                            ),
                        ]),
                    ]),
                ])->icon('chart-bar'),
                Tab::make('Коммуникации', [
                    Box::make('Публикации и письма', [
                        Grid::make([
                            $this->announcementStatus('В очереди на публикацию', AnnouncementStatus::Pending, 'clock'),
                            $this->announcementStatus('Отправлено в каналы', AnnouncementStatus::Sent, 'paper-airplane'),
                            $this->announcementStatus('Ошибки публикации', AnnouncementStatus::Failed, 'exclamation-triangle'),
                            $this->count('Отправленные письма', SentEmail::class, 'envelope'),
                        ]),
                        Flex::make([
                            ActionButton::make('Публикации вопросов', app(QuestionAnnouncementResource::class)->getIndexPageUrl())
                                ->icon('paper-airplane'),
                            ActionButton::make('Журнал писем', app(SentEmailResource::class)->getIndexPageUrl())
                                ->icon('envelope'),
                        ])->justifyAlign('start'),
                    ]),
                ])->icon('megaphone'),
                Tab::make('Аналитика', [
                    Box::make('Активность за 14 дней', [
                        Grid::make([
                            $this->activityChart(),
                            $this->questionStatusChart(),
                        ]),
                    ]),
                ])->icon('chart-pie'),
            ]),
        ];
    }

    private function activityChart(): LineChartMetric
    {
        return LineChartMetric::make('Новые записи и активность пользователей')
            ->line(app(DashboardMetrics::class)->activitySeries(), ['#7843E9', '#EC4176', '#16A085', '#F39C12'], ['area', 'line', 'line', 'line'])
            ->height(300)
            ->columnSpan(8);
    }

    private function questionStatusChart(): DonutChartMetric
    {
        return DonutChartMetric::make('Статусы вопросов')
            ->values(app(DashboardMetrics::class)->questionStatuses())
            ->colors(['#16A085', '#7843E9', '#EC4176'])
            ->decimals(0)
            ->height(300)
            ->columnSpan(4);
    }

    /**
     * Сколько пользователей с подтверждённой почтой заходили с указанного момента.
     * Считаем по last_seen_at: он хранит последний визит, поэтому «не раньше X» значит «был на сайте после X».
     */
    private function seen(string $label, string $icon, Carbon $since): ValueMetric
    {
        return ValueMetric::make($label)
            ->icon($icon)
            ->value(fn () => User::where('last_seen_at', '>=', $since)->count())
            ->columnSpan(4);
    }

    /**
     * Подтверждённые пользователи из общего числа.
     */
    private function users(): ValueMetric
    {
        return ValueMetric::make('Пользователи: с подтверждённой почтой из всех')
            ->icon('users')
            ->value(fn () => number_format(User::whereNotNull('email_verified_at')->count(), 0, ',', ' ')
                .' из '.number_format(User::count(), 0, ',', ' '))
            ->columnSpan(4);
    }

    private function count(string $label, string $model, string $icon): ValueMetric
    {
        return ValueMetric::make($label)
            ->icon($icon)
            ->value(fn () => $model::count())
            ->columnSpan(4);
    }

    private function postStatus(string $label, PostStatus $status, string $icon): ValueMetric
    {
        return ValueMetric::make($label)
            ->icon($icon)
            ->value(fn () => Post::where('status', $status)->count())
            ->columnSpan(4);
    }

    private function unansweredQuestions(): ValueMetric
    {
        return ValueMetric::make('Вопросы без ответа')
            ->icon('question-mark-circle')
            ->value(fn () => Question::query()
                ->where('created_at', '>=', Question::SOLUTION_TRACKING_SINCE)
                ->unanswered()
                ->count())
            ->columnSpan(4);
    }

    private function recentComments(): ValueMetric
    {
        return ValueMetric::make('Обсуждения за 24 часа')
            ->icon('chat-bubble-left-right')
            ->value(fn () => Comment::where('created_at', '>=', now()->subDay())->count())
            ->columnSpan(4);
    }

    private function announcementStatus(string $label, AnnouncementStatus $status, string $icon): ValueMetric
    {
        return ValueMetric::make($label)
            ->icon($icon)
            ->value(fn () => QuestionAnnouncement::where('status', $status)->count())
            ->columnSpan(3);
    }

    private function linkedMetric(ValueMetric $metric, string $url, string $label): Column
    {
        $navigate = 'window.location.href = '.json_encode($url, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).';';

        return Column::make([
            $metric->customAttributes([
                'class' => 'cursor-pointer transition hover:-translate-y-0.5 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-purple-500',
                'role' => 'link',
                'tabindex' => '0',
                'aria-label' => $label,
                'onclick' => $navigate,
                'onkeydown' => "if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); {$navigate} }",
            ]),
        ], 4);
    }
}
