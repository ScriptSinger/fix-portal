<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PruneUnverifiedUsers extends Command
{
    protected $signature = 'users:prune-unverified
        {--days=30 : Удалять только аккаунты старше N дней}
        {--apply : Реально удалить. Без флага команда только показывает, сколько нашлось}';

    protected $description = 'Мягко удаляет пользователей без подтверждённого e-mail и без какой-либо активности (спам-регистрации)';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $query = $this->candidates($days);
        $total = (clone $query)->count();

        $this->info("Кандидатов на удаление (без подтверждения, без активности, старше {$days} дн.): {$total}");

        if ($total === 0) {
            return self::SUCCESS;
        }

        $sample = (clone $query)->orderBy('id')->limit(5)->get(['id', 'name', 'created_at']);
        $this->table(['id', 'name', 'created_at'], $sample->map(fn (User $u) => [$u->id, $u->name, $u->created_at])->all());

        if (! $this->option('apply')) {
            $this->comment('Это пробный прогон. Для удаления добавьте --apply (удаление мягкое, записи можно восстановить).');

            return self::SUCCESS;
        }

        $deleted = 0;

        // Без events-цепочки на каждый аккаунт: у кандидатов нет комментариев и вопросов, каскадить нечего
        (clone $query)->select('id')->chunkById(500, function ($users) use (&$deleted): void {
            $deleted += User::withoutEvents(fn () => User::whereIn('id', $users->pluck('id'))->delete());
        });

        $this->info("Удалено (soft delete): {$deleted}");

        return self::SUCCESS;
    }

    private function candidates(int $days): Builder
    {
        $hasActivity = fn (string $table, string $column = 'user_id') => DB::table($table)
            ->whereColumn("{$table}.{$column}", 'users.id')
            ->select(DB::raw(1));

        $query = User::query()
            ->whereNull('email_verified_at')
            ->where('created_at', '<', now()->subDays($days))
            ->whereNotExists($hasActivity('comments'))
            ->whereNotExists($hasActivity('replies'))
            ->whereNotExists($hasActivity('questions'))
            ->whereNotExists($hasActivity('images'))
            ->whereNotExists($hasActivity('likes'))
            ->whereNotExists($hasActivity('dislikes'))
            ->whereNotExists($hasActivity('avatars'));

        // Кто заходил на сайт (старый лог запросов в БД), тот, возможно, живой человек,
        // у которого не дошло письмо подтверждения: такие аккаунты не трогаем
        if (Schema::hasTable('request_logs')) {
            $query->whereNotExists($hasActivity('request_logs'));
        }

        return $query;
    }
}
