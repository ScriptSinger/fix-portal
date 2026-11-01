<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Firmware;
use App\Models\Post;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

final class DashboardMetrics
{
    private const PERIOD_DAYS = 14;

    /**
     * @return array<string, array<string, int>>
     */
    public function activitySeries(): array
    {
        return Cache::remember('dashboard.metrics.activity.v1', now()->addMinutes(5), function (): array {
            return [
                'Вопросы' => $this->createdByDay(Question::class),
                'Статьи' => $this->createdByDay(Post::class),
                'Прошивки' => $this->createdByDay(Firmware::class),
                'Активные пользователи' => $this->lastSeenByDay(),
            ];
        });
    }

    /**
     * @return array<string, int>
     */
    public function questionStatuses(): array
    {
        return Cache::remember('dashboard.metrics.question-statuses.v1', now()->addMinutes(5), function (): array {
            $trackedQuestions = Question::query()
                ->where('created_at', '>=', Question::SOLUTION_TRACKING_SINCE);

            return [
                'Решены' => (clone $trackedQuestions)->whereNotNull('solved_at')->count(),
                'Без ответа' => (clone $trackedQuestions)->unanswered()->count(),
                'Есть ответ' => (clone $trackedQuestions)
                    ->whereNull('solved_at')
                    ->whereHas(
                        'comments',
                        static fn ($comments) => $comments->whereColumn('comments.user_id', '!=', 'questions.user_id'),
                    )
                    ->count(),
            ];
        });
    }

    /**
     * @return array<string, int>
     */
    private function createdByDay(string $model): array
    {
        return $this->fillDates(
            $model::query()
                ->where('created_at', '>=', $this->periodStart())
                ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
                ->groupBy('day')
                ->pluck('total', 'day')
                ->map(static fn ($value): int => (int) $value)
                ->all(),
        );
    }

    /**
     * @return array<string, int>
     */
    private function lastSeenByDay(): array
    {
        return $this->fillDates(
            User::query()
                ->where('last_seen_at', '>=', $this->periodStart())
                ->selectRaw('DATE(last_seen_at) as day, COUNT(*) as total')
                ->groupBy('day')
                ->pluck('total', 'day')
                ->map(static fn ($value): int => (int) $value)
                ->all(),
        );
    }

    /**
     * @param  array<string, int>  $values
     * @return array<string, int>
     */
    private function fillDates(array $values): array
    {
        $result = [];

        for ($day = self::PERIOD_DAYS - 1; $day >= 0; $day--) {
            $date = Carbon::today()->subDays($day)->toDateString();
            $result[$date] = $values[$date] ?? 0;
        }

        return $result;
    }

    private function periodStart(): string
    {
        return Carbon::today()->subDays(self::PERIOD_DAYS - 1)->startOfDay()->toDateTimeString();
    }
}
