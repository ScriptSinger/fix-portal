<?php
namespace App\Telegram\Handlers;

use App\Services\TelegramBotApi;
use App\Services\TelegramQuestionIntake;
use App\Services\TelegramQuestionModerationService;
use App\Jobs\ExpireTelegramQuestionPrompt;
use App\Enums\TelegramQuestionConfirmationStatus;
use Illuminate\Support\Facades\DB;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class MessageHandler
{
    public function __invoke(Nutgram $bot): void
    {
        $payload = $bot->update()->toArray();
        $message = $payload['message'];
        $text = trim((string) ($message['text'] ?? $message['caption'] ?? ''));
        if (str_starts_with($text, '/') || ($text === '' && empty($message['photo']))) {
            return;
        }
        $intake = app(TelegramQuestionIntake::class);
        if ($intake->addAlbumPhoto($message)) {
            return;
        }
        if ($continuation = $intake->pendingReplyContinuation($payload)) {
            app(TelegramQuestionModerationService::class)->attachCommunity($continuation->id, $payload);
            return;
        }
        if (! $id = $intake->storeAwaitingConfirmation($bot->update()->update_id, $payload)) {
            return;
        }
        if (DB::table('telegram_question_messages')->where('id', $id)->whereNotNull('confirmation_prompt_message_id')->exists()) {
            return;
        }
        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(InlineKeyboardButton::make('📋 Зарегистрировать', callback_data: "question_confirm:{$id}"))
            ->addRow(InlineKeyboardButton::make('❌ Не отправлять', callback_data: "question_cancel:{$id}"));
        $thread = (int) ($message['message_thread_id'] ?? 0);
        $sent = app(TelegramBotApi::class)->call('sendMessage', array_filter([
            'chat_id' => $message['chat']['id'], 'reply_to_message_id' => $message['message_id'],
            'message_thread_id' => $thread > 1 ? $thread : null,
            'text' => 'Похоже, это вопрос. Зарегистрировать его?', 'disable_web_page_preview' => true,
            'reply_markup' => json_encode($keyboard->toArray(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ], static fn ($value) => $value !== null));
        $promptId = (int) ($sent['message_id'] ?? 0);
        if ($promptId <= 0) {
            throw new \RuntimeException('Telegram confirmation prompt response has no message_id.');
        }
        DB::transaction(function () use ($id, $promptId): void {
            $record = DB::table('telegram_question_messages')->where('id', $id)->lockForUpdate()->first();
            if (! $record || $record->community_confirmation_status !== TelegramQuestionConfirmationStatus::Pending->value) {
                return;
            }
            $expiresAt = now()->addMinutes(max(1, (int) config('services.telegram.question_confirmation_minutes', 5)));
            DB::table('telegram_question_messages')->where('id', $id)->update([
                'confirmation_prompt_message_id' => $promptId,
                'confirmation_expires_at' => $expiresAt,
                'updated_at' => now(),
            ]);
            ExpireTelegramQuestionPrompt::dispatch($id, $promptId)->delay($expiresAt)->afterCommit();
        });
    }
}
