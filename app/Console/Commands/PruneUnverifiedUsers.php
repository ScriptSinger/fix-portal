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
        {--days=7 : Удалять только аккаунты старше N дней}
        {--apply : Реально удалить. Без флага команда только показывает, сколько нашлось}';

    protected $description = 'Навсегда удаляет пользователей, не подтвердивших e-mail за N дней и не оставивших никакой активности (спам-регистрации). Освобождает адрес: человек может зарегистрироваться заново';

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
            $this->comment('Это пробный прогон. Для удаления добавьте --apply (удаление окончательное).');

            return self::SUCCESS;
        }

        $deleted = 0;

        (clone $query)->select('id')->chunkById(500, function ($users) use (&$deleted, $days): void {
            foreach ($users as $user) {
                $deleted += DB::transaction(function () use ($user, $days): int {
                    $candidate = $this->candidates($days)->whereKey($user->id)->lockForUpdate()->first();

                    return $candidate ? (int) $candidate->delete() : 0;
                });
            }
        });

        $this->info("Удалено навсегда: {$deleted}");

        return self::SUCCESS;
    }

    private function candidates(int $days): Builder
    {
        $hasActivity = fn (string $table, string $column = 'user_id') => DB::table($table)
            ->whereColumn("{$table}.{$column}", 'users.id')
            ->select(DB::raw(1));

        $query = User::query()
            ->whereNotIn('id', config('users.protected_ids', []))
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
