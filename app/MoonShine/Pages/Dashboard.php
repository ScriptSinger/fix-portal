<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Models\Firmware;
use App\Models\Post;
use App\Models\Question;
use App\Models\User;
use App\MoonShine\Resources\VisitorResource;
use Illuminate\Support\Carbon;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\Pages\Page;
use MoonShine\MenuManager\Attributes\SkipMenu;
use MoonShine\UI\Components\Layout\Grid;
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
                $this->count('Прошивки', Firmware::class, 'cpu-chip'),
                $this->count('Пользователи', User::class, 'users'),
                $this->seen('Онлайн сейчас (за ' . User::ONLINE_MINUTES . ' мин)', 'signal', now()->subMinutes(User::ONLINE_MINUTES), VisitorResource::TAG_ONLINE),
                $this->seen('Заходили сегодня', 'sun', VisitorResource::startOfToday(), VisitorResource::TAG_TODAY),
            ]),
        ];
    }

    /**
     * Сколько пользователей с подтверждённой почтой заходили с указанного момента.
     * Считаем по last_seen_at: он хранит последний визит, поэтому «не раньше X» значит «был на сайте после X».
     */
    private function seen(string $label, string $icon, Carbon $since, string $tag): ValueMetric
    {
        // Карточка кликабельна: ведёт в раздел «Посетители» на соответствующую вкладку
        $url = app(VisitorResource::class)->getIndexPageUrl(['query-tag' => $tag]);

        return ValueMetric::make($label)
            ->icon($icon)
            ->value(fn () => User::where('last_seen_at', '>=', $since)->count())
            ->customAttributes(['onclick' => "window.location.href='" . e($url) . "'", 'style' => 'cursor: pointer'])
            ->columnSpan(4);
    }

    private function count(string $label, string $model, string $icon): ValueMetric
    {
        return ValueMetric::make($label)
            ->icon($icon)
            ->value(fn () => $model::count())
            ->columnSpan(4);
    }
}
