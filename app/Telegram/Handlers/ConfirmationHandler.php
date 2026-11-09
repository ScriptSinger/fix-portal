<?php
namespace App\Telegram\Handlers;

use App\Actions\TelegramQuestions\ConfirmRegistration;
use App\Services\TelegramBotApi;
use App\Services\TelegramPromptDeletion;
use App\Services\TelegramRegistrationPrompt;
use Illuminate\Support\Facades\DB;
use SergiX44\Nutgram\Nutgram;

class ConfirmationHandler
{
    public function __invoke(Nutgram $bot): void
    {
        $callback = $bot->callbackQuery()->toArray();
        preg_match('/^question_(confirm|cancel):(\d+)$/', $callback['data'], $matches);
        $id = (int) $matches[2];
        $result = ConfirmRegistration::run($id, (int) data_get($callback, 'from.id'), (int) data_get($callback, 'message.chat.id'), $matches[1] === 'confirm');
        $api = app(TelegramBotApi::class);
        try {
            $api->call('answerCallbackQuery', ['callback_query_id' => $callback['id'], 'text' => $result['message'], 'show_alert' => ! $result['edit']]);
        } catch (\RuntimeException $exception) {
            // A callback may already have expired when its persisted update is retried.
            // Its acknowledgement must not block delivery of the durable result.
            report($exception);
        }
        if ($result['delete'] ?? false) {
            $chat = (int) data_get($callback, 'message.chat.id');
            $prompt = (int) data_get($callback, 'message.message_id');
            app(TelegramPromptDeletion::class)->delete($chat, $prompt);
            DB::table('telegram_question_messages')->where('id', $id)
                ->where('confirmation_prompt_message_id', $prompt)
                ->update(['confirmation_prompt_message_id' => null, 'updated_at' => now()]);
            return;
        }
        if ($result['edit']) {
            app(TelegramRegistrationPrompt::class)->confirm($id,
                (int) data_get($callback, 'message.chat.id'),
                (int) data_get($callback, 'message.message_id'));
        }
    }
}
