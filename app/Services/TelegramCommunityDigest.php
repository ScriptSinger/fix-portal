<?php

namespace App\Services;

use App\Enums\TelegramQuestionResolutionStatus;
use Illuminate\Support\Facades\DB;

class TelegramCommunityDigest
{
    /** HTML fragment for the existing weekly digest; this service never sends. */
    public function text(): ?string
    {
        $sourceChatId = (int) config('services.telegram.source_chat_id');
        if ($sourceChatId === 0) {
            return null;
        }

        $open = DB::table('telegram_question_messages')
            ->where('telegram_chat_id', $sourceChatId)
            ->whereNotNull('registered_at')
            ->where('resolution_status', TelegramQuestionResolutionStatus::Open->value)
            ->whereNull('archived_at')->count();

        // Reputation is global and all-time; reopening a question does not erase awards.
        $leaders = DB::table('telegram_solution_awards')
            ->select('telegram_user_id')->selectRaw('COUNT(*) AS solutions')
            ->where('telegram_user_id', '>', 0)
            ->groupBy('telegram_user_id')
            ->orderByDesc('solutions')->orderBy('telegram_user_id')->limit(10)->get();

        if ($open === 0 && $leaders->isEmpty()) {
            return null;
        }

        $lines = ["Открытых вопросов в Telegram: {$open}."];
        if ($leaders->isNotEmpty()) {
            $lines[] = "\n<b>Помощники за всё время</b>";
            foreach ($leaders as $index => $leader) {
                $userId = (int) $leader->telegram_user_id;
                $count = (int) $leader->solutions;
                $rank = htmlspecialchars(TelegramReputation::rank($count) ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $lines[] = ($index + 1).". <a href=\"tg://user?id={$userId}\">ID {$userId}</a> — карма: {$count}, {$rank}";
            }
        }

        return implode("\n", $lines);
    }
}
