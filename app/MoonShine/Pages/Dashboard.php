<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Models\Comment;
use App\Models\Firmware;
use App\Models\Post;
use App\Models\Question;
use App\Models\Reply;
use App\Models\User;
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
                $this->count('Комментарии', Comment::class, 'chat-bubble-left-right'),
                $this->count('Ответы', Reply::class, 'arrow-uturn-left'),
                $this->count('Прошивки', Firmware::class, 'cpu-chip'),
                $this->count('Пользователи', User::class, 'users'),
                ValueMetric::make('Онлайн сейчас (за ' . User::ONLINE_MINUTES . ' мин)')
                    ->icon('signal')
                    ->value(fn () => User::online()->count())
                    ->columnSpan(4),
                ValueMetric::make('Регистраций за 30 дней')
                    ->icon('user-plus')
                    ->value(fn () => User::where('created_at', '>=', now()->subDays(30))->count())
                    ->columnSpan(4),
            ]),
        ];
    }

    private function count(string $label, string $model, string $icon): ValueMetric
    {
        return ValueMetric::make($label)
            ->icon($icon)
            ->value(fn () => $model::count())
            ->columnSpan(4);
    }
}
