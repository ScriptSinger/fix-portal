<?php

namespace App\Telegram\Handlers;

use App\Services\TelegramReputation;
use App\Services\TelegramBotApi;
use Illuminate\Support\Facades\DB;

class ReactionSolutionHandler
{
    public function handle(array $reaction): void
    {
        $chatId = (int) data_get($reaction, 'chat.id');
        $authorId = (int) data_get($reaction, 'user.id');
        $messageId = (int) ($reaction['message_id'] ?? 0);
        if ($chatId === 0 || $authorId === 0 || $messageId <= 0
            || (string) $chatId !== (string) config('services.telegram.source_chat_id')
            || data_get($reaction, 'user.is_bot') === true
            || ! $this->addedThumbsUp($reaction)) {
            return;
        }

        // Telegram supplies an ID, not the reacted-to message. Its author is
        // read from the authenticated message update previously saved by us.
        $stored = DB::table('telegram_webhook_updates')
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.message.chat.id')) = ?", [(string) $chatId])
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.message.message_id')) = ?", [(string) $messageId])
            ->latest('update_id')
            ->first(['payload']);
        $answer = data_get(json_decode((string) ($stored->payload ?? '{}'), true), 'message');
        $helperId = (int) data_get($answer, 'from.id');
        if (! is_array($answer) || $helperId === 0 || $helperId === $authorId
            || data_get($answer, 'from.is_bot') === true || isset($answer['sender_chat'])) {
            return;
        }

        $threadId = (int) ($answer['message_thread_id'] ?? 0);
        $threadId = $threadId > 1 ? $threadId : 0;
        $questions = DB::table('telegram_question_messages')
            ->where('telegram_chat_id', $chatId)
            ->where('telegram_thread_id', $threadId)
            ->where('telegram_user_id', $authorId)
            ->whereNotNull('registered_at')
            ->whereNull('archived_at')
            ->where('resolution_status', 'open')
            ->where('telegram_message_id', '<', $messageId)
            ->orderByDesc('id')->limit(2)->get(['id']);

        if ($questions->isEmpty()) {
            return;
        }

        $message = [
            'message_id' => $messageId,
            'from' => ['id' => $authorId, 'is_bot' => false],
            'chat' => ['id' => $chatId],
            'message_thread_id' => $threadId,
            'reply_to_message' => $answer,
        ];
        if ($questions->count() === 1) {
            $result = app(TelegramReputation::class)->resolve($message, (int) $questions->first()->id);
            if (str_starts_with($result, 'Вопрос отмечен решённым.')) {
                $this->acknowledge($chatId, $messageId);
            }
            return;
        }

        // Only this exceptional case needs one prompt to identify the question.
        app(OfferSolutionHandler::class)->handle($message);
    }

    public function acknowledge(int $chatId, int $messageId): void
    {
        try {
            app(TelegramBotApi::class)->call('setMessageReaction', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'reaction' => [['type' => 'emoji', 'emoji' => '🎉']],
            ]);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    private function addedThumbsUp(array $reaction): bool
    {
        $hasThumbsUp = static fn (array $items): bool => collect($items)->contains(
            static fn ($item): bool => data_get($item, 'type') === 'emoji' && data_get($item, 'emoji') === '👍'
        );

        return $hasThumbsUp((array) ($reaction['new_reaction'] ?? []))
            && ! $hasThumbsUp((array) ($reaction['old_reaction'] ?? []));
    }
}
