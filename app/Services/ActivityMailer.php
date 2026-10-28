<?php

namespace App\Services;

use App\Jobs\SendActivityEmail;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Письма об активности: ответили на вопрос, ответ отмечен решением. Правила, чтобы не раздражать и не упереться в лимит ящика:
 * письмо уходит не сразу, а через несколько минут и одно на всё накопившееся; не уходит, если человек уже прочитал
 * уведомление на сайте, отключил письма или не подтвердил почту; на человека и на весь сайт есть суточный потолок.
 */
class ActivityMailer
{
    /** Какие события идут письмом. Реакции и ответы на комментарии остаются только в колокольчике. */
    public const KINDS = [ActivityNotification::ANSWER, ActivityNotification::SOLUTION];

    public function enabled(): bool
    {
        return (bool) config('mail.activity.enabled');
    }

    /** Письма этому человеку допустимы: включены у него, адрес подтверждён. */
    public function wantsMail(User $user): bool
    {
        return $user->email_notifications && $user->hasVerifiedEmail() && filled($user->email);
    }

    /** Новое событие: ставит отправку на ближайшее окно. Пока окно открыто, следующие события в него же и попадут. */
    public function schedule(User $user): void
    {
        if (! $this->enabled() || ! $this->wantsMail($user)) {
            return;
        }

        $delay = (int) config('mail.activity.delay_minutes');

        if (Cache::add($this->lockKey($user), true, now()->addMinutes($delay + 5))) {
            SendActivityEmail::dispatch($user->id)->delay(now()->addMinutes($delay))->afterCommit();
        }
    }

    /** Окно закрывается в начале отправки: события, пришедшие позже, откроют новое. */
    public function closeWindow(User $user): void
    {
        Cache::forget($this->lockKey($user));
    }

    /** Непрочитанные и ещё не отправленные письмом уведомления человека за последние сутки. */
    public function pending(User $user): Collection
    {
        return $user->notifications()
            ->whereNull('read_at')
            ->whereNull('emailed_at')
            ->whereIn('data->kind', self::KINDS)
            ->where('created_at', '>=', now()->subDay())
            ->oldest()
            ->get();
    }

    public function withinLimits(User $user): bool
    {
        return (int) Cache::get($this->dailyKey($user), 0) < (int) config('mail.activity.per_user_daily')
            && (int) Cache::get($this->dailyKey(), 0) < (int) config('mail.activity.daily_cap');
    }

    /** @param Collection<int, DatabaseNotification> $notifications */
    public function markSent(User $user, Collection $notifications): void
    {
        DatabaseNotification::whereIn('id', $notifications->pluck('id'))->update(['emailed_at' => now()]);

        foreach ([$this->dailyKey($user), $this->dailyKey()] as $key) {
            Cache::add($key, 0, now()->addDay());
            Cache::increment($key);
        }
    }

    private function lockKey(User $user): string
    {
        return "activity-mail:window:{$user->id}";
    }

    /** Счётчик писем за сутки: на человека или (без аргумента) на весь сайт. */
    private function dailyKey(?User $user = null): string
    {
        return 'activity-mail:daily:' . now()->format('Y-m-d') . ':' . ($user?->id ?? 'all');
    }
}
