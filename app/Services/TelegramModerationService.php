<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class TelegramModerationService
{
    public function execute(string $command, array $arguments, int $moderatorTelegramId): string
    {
        $userId = (int) ($arguments[0] ?? 0);
        if ($userId < 1) {
            return 'Укажите ID пользователя сайта: /'.$command.' <user_id>';
        }

        $user = User::query()->find($userId);
        if (! $user) {
            return 'Пользователь сайта с таким ID не найден.';
        }

        return DB::transaction(function () use ($command, $arguments, $moderatorTelegramId, $user): string {
            $parameters = ['arguments' => array_values($arguments)];

            return match ($command) {
                '/mute' => $this->mute($user, $moderatorTelegramId, $parameters),
                '/unmute' => $this->unmute($user, $moderatorTelegramId, $parameters),
                '/block' => $this->block($user, $moderatorTelegramId, $parameters),
                '/unblock' => $this->unblock($user, $moderatorTelegramId, $parameters),
                default => 'Неизвестная команда модерации.',
            };
        });
    }

    /** @param array<string, mixed> $parameters */
    private function mute(User $user, int $moderatorTelegramId, array $parameters): string
    {
        $minutes = max(1, min((int) ($parameters['arguments'][1] ?? config('services.telegram.mute_minutes', 1440)), 43200));
        $user->forceFill(['muted_until' => now()->addMinutes($minutes)])->save();
        $this->audit($moderatorTelegramId, $user->id, 'mute', $parameters + ['minutes' => $minutes]);

        return "Пользователь #{$user->id} заглушён на {$minutes} мин.";
    }

    /** @param array<string, mixed> $parameters */
    private function unmute(User $user, int $moderatorTelegramId, array $parameters): string
    {
        $user->forceFill(['muted_until' => null])->save();
        $this->audit($moderatorTelegramId, $user->id, 'unmute', $parameters);

        return "Мьют пользователя #{$user->id} снят.";
    }

    /** @param array<string, mixed> $parameters */
    private function block(User $user, int $moderatorTelegramId, array $parameters): string
    {
        $user->forceFill([
            'blocked_at' => now(),
            'moderation_reason' => isset($parameters['arguments'][1]) ? implode(' ', array_slice($parameters['arguments'], 1)) : null,
        ])->save();
        $this->audit($moderatorTelegramId, $user->id, 'block', $parameters);

        return "Пользователь #{$user->id} заблокирован.";
    }

    /** @param array<string, mixed> $parameters */
    private function unblock(User $user, int $moderatorTelegramId, array $parameters): string
    {
        $user->forceFill(['blocked_at' => null, 'moderation_reason' => null])->save();
        $this->audit($moderatorTelegramId, $user->id, 'unblock', $parameters);

        return "Блокировка пользователя #{$user->id} снята.";
    }

    /** @param array<string, mixed> $parameters */
    private function audit(int $moderatorTelegramId, int $userId, string $action, array $parameters): void
    {
        DB::table('telegram_moderation_actions')->insert([
            'moderator_telegram_id' => $moderatorTelegramId,
            'user_id' => $userId,
            'action' => $action,
            'parameters' => json_encode($parameters, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
