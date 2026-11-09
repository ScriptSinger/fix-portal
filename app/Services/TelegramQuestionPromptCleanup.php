<?php

namespace App\Services;

use App\Jobs\ExpireTelegramQuestionPrompt;
use Illuminate\Support\Facades\DB;

class TelegramQuestionPromptCleanup
{
    public function run(): void
    {
        $prompts = DB::table('telegram_question_messages')
            ->whereNotNull('confirmation_prompt_message_id')
            ->whereIn('community_confirmation_status', ['confirmed', 'declined'])
            ->orderBy('updated_at')->limit(100)
            ->get(['id', 'confirmation_prompt_message_id']);

        foreach ($prompts as $prompt) {
            try {
                (new ExpireTelegramQuestionPrompt((int) $prompt->id, (int) $prompt->confirmation_prompt_message_id))
                    ->handle(app(TelegramPromptDeletion::class));
            } catch (\Throwable $exception) {
                report($exception);
            }
        }
    }
}
