<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Models\Comment;
use App\Models\Firmware;
use App\Models\Post;
use App\Models\Question;
use App\Models\Reply;
use App\Models\User;
use App\MoonShine\Resources\UserResource;
use Illuminate\Support\Carbon;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\Pages\Page;
use MoonShine\MenuManager\Attributes\SkipMenu;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Link;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Components\Metrics\Wrapped\ValueMetric;

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
            Grid::make([
                $this->count('Статьи', Post::class, 'newspaper'),
                $this->count('Вопросы', Question::class, 'question-mark-circle'),
                $this->count('Комментарии', Comment::class, 'chat-bubble-left-right'),
                $this->count('Ответы', Reply::class, 'arrow-uturn-left'),
                $this->count('Прошивки', Firmware::class, 'cpu-chip'),
                $this->count('Пользователи', User::class, 'users'),
                $this->seen('Онлайн сейчас (за ' . User::ONLINE_MINUTES . ' мин)', 'signal', now()->subMinutes(User::ONLINE_MINUTES)),
                $this->seen('Заходили сегодня', 'sun', $this->startOfToday()),
                $this->seen('Заходили за 7 дней', 'calendar-days', now()->subDays(7)),
                $this->seen('Заходили за 30 дней', 'calendar', now()->subDays(30)),
                $this->registered('Регистраций сегодня', $this->startOfToday()),
                $this->registered('Регистраций за 7 дней', now()->subDays(7)),
                $this->registered('Регистраций за 30 дней', now()->subDays(30)),
                Column::make([Box::make('Недавние посетители', [$this->recentVisitors()])], colSpan: 12),
            ]),
        ];
    }

    /**
     * Сутки считаем по времени сайта (Екатеринбург), а в запрос отдаём UTC, как лежит в базе.
     */
    private function startOfToday(): Carbon
    {
        return now()->setTimezone('Asia/Yekaterinburg')->startOfDay()->utc();
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
            ->columnSpan(3);
    }

    private function registered(string $label, Carbon $since): ValueMetric
    {
        return ValueMetric::make($label)
            ->icon('user-plus')
            ->value(fn () => User::where('created_at', '>=', $since)->count())
            ->columnSpan(4);
    }

    private function recentVisitors(): TableBuilder
    {
        $resource = app(UserResource::class);

        $items = User::whereNotNull('last_seen_at')
            ->orderByDesc('last_seen_at')
            ->limit(10)
            ->get(['id', 'name', 'email', 'last_seen_at'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'seen' => $user->last_seen_at->diffForHumans() . ' (' . $user->last_seen_at->setTimezone('Asia/Yekaterinburg')->format('d.m H:i') . ')',
                'online' => $user->last_seen_at->gte(now()->subMinutes(User::ONLINE_MINUTES)) ? '● онлайн' : '',
                'url' => $resource->getDetailPageUrl($user->id),
            ])
            ->all();

        return TableBuilder::make(
            fields: [
                Preview::make('Пользователь', 'name', fn (array $row) => (string) Link::make($row['url'], $row['name'])),
                Text::make('E-mail', 'email'),
                Text::make('Последний визит', 'seen'),
                Text::make('', 'online'),
            ],
            items: $items,
        )->simple();
    }

    private function count(string $label, string $model, string $icon): ValueMetric
    {
        return ValueMetric::make($label)
            ->icon($icon)
            ->value(fn () => $model::count())
            ->columnSpan(4);
    }
}
