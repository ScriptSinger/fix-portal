<?php

namespace App\Services;

use App\Actions\TelegramQuestions\ResolveQuestion;
use Illuminate\Support\Facades\DB;

class TelegramReputation
{
    public const RANKS = Reputation::RANKS;

    public function countFor(int $telegramUserId): int
    {
        return (int) DB::table('telegram_solution_awards')
            ->where('telegram_user_id', $telegramUserId)
            ->count();
    }

    public function rankFor(int $telegramUserId): ?string
    {
        return self::rank($this->countFor($telegramUserId));
    }

    public static function rank(int $count): ?string
    {
        foreach (self::RANKS as $threshold => $rank) {
            if ($count >= $threshold) {
                return $rank;
            }
        }

        return null;
    }

    /** @param array<string, mixed> $message */
    public function resolve(array $message, ?int $questionId = null, bool $acknowledge = false): string
    {
        return ResolveQuestion::run($message, $questionId, $acknowledge);
    }
}
