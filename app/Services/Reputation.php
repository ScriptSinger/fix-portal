<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * Репутация = число ответов пользователя, которые авторы вопросов отметили решением.
 * Свои вопросы не считаются. Лайки и реакции в репутацию не входят.
 */
class Reputation
{
    /** Пороги званий: от скольких решений даётся звание */
    public const RANKS = [10 => 'Эксперт', 3 => 'Знаток', 1 => 'Помощник'];

    /**
     * Решения по пользователям: [user_id => число].
     *
     * @param  list<int>|null  $userIds  null: все
     * @param  \DateTimeInterface|null  $since  считать решения, отмеченные не раньше этой даты
     */
    public static function counts(?array $userIds = null, ?\DateTimeInterface $since = null): Collection
    {
        $total = collect();
        foreach ([['comment', 'comments'], ['reply', 'replies']] as [$type, $table]) {
            $rows = DB::table('questions as q')
                ->join("$table as m", fn ($j) => $j->on('m.id', '=', 'q.solution_id')->where('q.solution_type', '=', $type))
                ->whereNull('q.deleted_at')->whereNull('m.deleted_at')
                ->whereColumn('m.user_id', '!=', 'q.user_id')
                ->when($userIds !== null, fn ($q) => $q->whereIn('m.user_id', $userIds))
                ->when($since, fn ($q) => $q->where('q.solved_at', '>=', $since))
                ->groupBy('m.user_id')->selectRaw('m.user_id as user_id, count(*) as n')->get();
            foreach ($rows as $row) {
                $total[$row->user_id] = ($total[$row->user_id] ?? 0) + $row->n;
            }
        }

        return $total;
    }

    public static function countFor(int $userId): int
    {
        return (int) (self::counts([$userId])[$userId] ?? 0);
    }

    /** Звание по числу решений или null, пока решений нет. */
    public static function rank(int $count): ?string
    {
        foreach (self::RANKS as $from => $title) {
            if ($count >= $from) {
                return $title;
            }
        }

        return null;
    }

    /** Сколько решений осталось до следующего звания: [название, сколько] или null на высшем. */
    public static function next(int $count): ?array
    {
        $steps = array_reverse(self::RANKS, true);
        foreach ($steps as $from => $title) {
            if ($count < $from) {
                return [$title, $from - $count];
            }
        }

        return null;
    }

    /** Доля пути до следующего звания (0..1) или null на высшем звании. */
    public static function progress(int $count): ?float
    {
        $from = 0;
        foreach (array_reverse(array_keys(self::RANKS)) as $threshold) {
            if ($count < $threshold) {
                return ($count - $from) / ($threshold - $from);
            }
            $from = $threshold;
        }

        return null;
    }

    /** Лучшие помощники за период: коллекция [User, число]. */
    public static function top(int $limit = 5, ?\DateTimeInterface $since = null): Collection
    {
        $counts = self::counts(null, $since)->sortDesc()->take($limit);
        $users = User::with('avatar')->whereIn('id', $counts->keys())->get()->keyBy('id');

        return $counts->map(fn ($n, $id) => ['user' => $users->get($id), 'count' => $n])->filter(fn ($row) => $row['user'])->values();
    }
}
