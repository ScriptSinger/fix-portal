<?php
namespace App\Telegram\Handlers;

use App\Services\TelegramBotApi;
use App\Services\TelegramQuestionIntake;
use App\Services\TelegramQuestionModerationService;
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
        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(InlineKeyboardButton::make('📋 Зарегистрировать', callback_data: "question_confirm:{$id}"))
            ->addRow(InlineKeyboardButton::make('❌ Не отправлять', callback_data: "question_cancel:{$id}"));
        $thread = (int) ($message['message_thread_id'] ?? 0);
        app(TelegramBotApi::class)->call('sendMessage', array_filter([
            'chat_id' => $message['chat']['id'], 'reply_to_message_id' => $message['message_id'],
            'message_thread_id' => $thread > 1 ? $thread : null,
            'text' => 'Похоже, это вопрос. Зарегистрировать его?', 'disable_web_page_preview' => true,
            'reply_markup' => json_encode($keyboard->toArray(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ], static fn ($value) => $value !== null));
    }
}
