<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use SergiX44\Nutgram\Nutgram;
use App\Telegram\PersistedUpdate;
use App\Telegram\Handlers\ReactionSolutionHandler;

/** Adapts authenticated, persisted webhook updates to Nutgram routing. */
class TelegramUpdateHandler
{
    public function handle(int $updateId, array $payload): void
    {
        if (is_array($payload['message_reaction'] ?? null)) {
            app(ReactionSolutionHandler::class)->handle($payload['message_reaction']);
        } elseif (is_array(data_get($payload, 'callback_query.message.chat')) || is_array(data_get($payload, 'message.chat'))) {
            $bot = app(Nutgram::class);
            $bot->setRunningMode(new PersistedUpdate($payload));
            $bot->run();
        }
        DB::table('telegram_webhook_updates')->where('update_id', $updateId)->update(['processed_at' => now()]);
    }
}
