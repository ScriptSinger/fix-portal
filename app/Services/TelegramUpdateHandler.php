<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class TelegramUpdateHandler
{
    public function __construct(
        private readonly TelegramBotApi $telegram,
        private readonly TelegramModerationService $moderation,
        private readonly TelegramQuestionIntake $questionIntake,
        private readonly TelegramQuestionModerationService $questionModeration,
    )
    {
    }

    /** @param array<string, mixed> $payload */
    public function handle(int $updateId, array $payload): void
    {
        if (is_array($payload['callback_query'] ?? null)) {
            $this->handleQuestionConfirmation($payload['callback_query']);
            $this->markProcessed($updateId);

            return;
        }

        $message = $payload['message'] ?? null;
        if (! is_array($message) || ! is_array($message['chat'] ?? null)) {
            $this->markProcessed($updateId);
            return;
        }

        $text = trim((string) ($message['text'] ?? $message['caption'] ?? ''));
        $hasPhoto = is_array($message['photo'] ?? null) && $message['photo'] !== [];
        $chatId = $message['chat']['id'] ?? null;
        if (($text === '' && ! $hasPhoto) || $chatId === null) {
            $this->markProcessed($updateId);
            return;
        }

        $parsed = $this->command($text);
        if ($parsed === null) {
            if ($text !== '' && str_starts_with(ltrim($text), '/')) {
                $this->markProcessed($updateId);

                return;
            }

            if ($continuation = $this->questionIntake->pendingReplyContinuation($payload)) {
                $this->questionModeration->attach((int) $continuation->id, $payload, null);
                $this->markProcessed($updateId);

                return;
            }

            if ($recordId = $this->questionIntake->storeAwaitingConfirmation($updateId, $payload)) {
                $this->sendQuestionConfirmation($recordId, $payload);
            }
            $this->markProcessed($updateId);
            return;
        }

        $telegramUserId = (int) ($message['from']['id'] ?? 0);
        $response = $this->isModerator($telegramUserId)
            ? $this->moderatorResponseWithLimits($updateId, $parsed[0], $parsed[1], $telegramUserId, $message)
            : 'Нет доступа к командам модератора.';

        if ($this->shouldSilenceSourceCommand($parsed[0], $chatId, $response)) {
            try {
                $this->telegram->call('deleteMessage', [
                    'chat_id' => $chatId,
                    'message_id' => (int) ($message['message_id'] ?? 0),
                ]);
                $this->markProcessed($updateId);

                return;
            } catch (\Throwable) {
                // If the bot is not an administrator, keep the response visible.
            }
        }

        $this->telegram->call('sendMessage', [
            'chat_id' => $chatId,
            'text' => $response,
            'disable_web_page_preview' => true,
        ]);

        $this->markProcessed($updateId);
    }

    /** @param array<string, mixed> $callback */
    private function handleQuestionConfirmation(array $callback): void
    {
        $data = (string) ($callback['data'] ?? '');
        if (! preg_match('/^question_(confirm|cancel):(\d+)$/', $data, $matches)) {
            return;
        }

        $recordId = (int) $matches[2];
        $action = $matches[1];
        $actorId = (int) data_get($callback, 'from.id');
        $callbackChatId = (int) data_get($callback, 'message.chat.id');
        $record = DB::table('telegram_question_messages')->where('id', $recordId)->first();

        if (! $record || (int) $record->telegram_user_id !== $actorId || (int) $record->telegram_chat_id !== $callbackChatId) {
            $this->answerCallback($callback, 'Подтвердить может только автор вопроса.', true);

            return;
        }

        if ($record->status !== 'awaiting_confirmation') {
            $this->answerCallback($callback, 'Этот вопрос уже обработан.', true);

            return;
        }

        $confirmationMinutes = max(1, (int) config('services.telegram.question_confirmation_minutes', 10));
        if ((string) $record->created_at < now()->subMinutes($confirmationMinutes)->toDateTimeString()) {
            DB::table('telegram_question_messages')->where('id', $recordId)->update([
                'status' => 'cancelled',
                'updated_at' => now(),
            ]);
            $this->answerCallback($callback, 'Срок подтверждения истёк.', true);
            $this->editConfirmationMessage($callback, '⌛ Срок подтверждения истёк.');

            return;
        }

        if ($action === 'cancel') {
            DB::table('telegram_question_messages')->where('id', $recordId)->update([
                'status' => 'cancelled',
                'updated_at' => now(),
            ]);
            $this->answerCallback($callback, 'Вопрос не отправлен.', false);
            $this->editConfirmationMessage($callback, '❌ Вопрос не отправлен на модерацию.');

            return;
        }

        $updated = DB::table('telegram_question_messages')
            ->where('id', $recordId)
            ->where('status', 'awaiting_confirmation')
            ->update([
                'status' => 'pending_moderation',
                'updated_at' => now(),
            ]);

        if ($updated !== 1) {
            $this->answerCallback($callback, 'Этот вопрос уже обработан.', true);

            return;
        }

        $payload = json_decode((string) $record->payload, true, 32, JSON_THROW_ON_ERROR);
        $this->answerCallback($callback, 'Вопрос отправлен на модерацию.', false);
        $this->editConfirmationMessage($callback, '✅ Вопрос отправлен на модерацию.');
        $this->questionModeration->preview((int) $record->telegram_update_id, $payload);
    }

    /** @param array<string, mixed> $payload */
    private function sendQuestionConfirmation(int $recordId, array $payload): void
    {
        $message = $payload['message'] ?? [];
        $chatId = (int) data_get($message, 'chat.id');
        $messageId = (int) data_get($message, 'message_id');
        $text = trim((string) data_get($message, 'text', data_get($message, 'caption', '')));

        $this->telegram->call('sendMessage', [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => "Похоже, это вопрос. Отправить его на модерацию?\n\n".Str::limit($text, 700),
            'disable_web_page_preview' => true,
            'reply_markup' => json_encode([
                'inline_keyboard' => [[
                    ['text' => '✅ Отправить', 'callback_data' => "question_confirm:{$recordId}"],
                    ['text' => '❌ Не отправлять', 'callback_data' => "question_cancel:{$recordId}"],
                ]],
            ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ]);
    }

    /** @param array<string, mixed> $callback */
    private function answerCallback(array $callback, string $text, bool $alert): void
    {
        $callbackId = (string) ($callback['id'] ?? '');
        if ($callbackId === '') {
            return;
        }

        $this->telegram->call('answerCallbackQuery', [
            'callback_query_id' => $callbackId,
            'text' => $text,
            'show_alert' => $alert,
        ]);
    }

    /** @param array<string, mixed> $callback */
    private function editConfirmationMessage(array $callback, string $text): void
    {
        $chatId = (int) data_get($callback, 'message.chat.id');
        $messageId = (int) data_get($callback, 'message.message_id');
        if ($chatId === 0 || $messageId === 0) {
            return;
        }

        $this->telegram->call('editMessageText', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'reply_markup' => json_encode(['inline_keyboard' => []], JSON_THROW_ON_ERROR),
        ]);
    }

    /** @param list<string> $arguments */
    private function moderatorResponseWithLimits(
        int $updateId,
        string $command,
        array $arguments,
        int $moderatorTelegramId,
        array $message,
    ): string {
        if (in_array($command, ['/question', '/attach'], true)) {
            $key = sprintf(
                'telegram-command:%s:%s:%s',
                $message['chat']['id'] ?? 0,
                $moderatorTelegramId,
                $command,
            );
            $limit = max(1, (int) config('services.telegram.command_rate_limit_per_minute', 10));

            if (RateLimiter::tooManyAttempts($key, $limit)) {
                return 'Слишком много команд. Повторите через '.RateLimiter::availableIn($key).' сек.';
            }

            RateLimiter::hit($key, 60);
        }

        return $this->moderatorResponse($updateId, $command, $arguments, $moderatorTelegramId, $message);
    }

    private function shouldSilenceSourceCommand(string $command, int|string $chatId, string $response): bool
    {
        if (! config('services.telegram.silent_source_commands') || (string) $chatId !== (string) config('services.telegram.source_chat_id')) {
            return false;
        }

        return match ($command) {
            '/question' => $response === 'Сообщение передано на модерацию.',
            '/attach' => str_starts_with($response, 'Сообщение добавлено к вопросу.')
                || str_starts_with($response, 'Сообщение и вложения добавлены к опубликованному вопросу.'),
            default => false,
        };
    }

    /** @return array{0: string, 1: list<string>}|null */
    private function command(string $text): ?array
    {
        $parts = preg_split('/\s+/u', trim($text)) ?: [];
        $firstWord = (string) array_shift($parts);
        $command = Str::lower(Str::before($firstWord, '@'));

        return in_array($command, ['/start', '/help', '/status', '/question', '/attach', '/mute', '/unmute', '/block', '/unblock'], true)
            ? [$command, array_values($parts)]
            : null;
    }

    private function isModerator(int $telegramUserId): bool
    {
        return $telegramUserId > 0 && in_array($telegramUserId, config('services.telegram.moderator_ids', []), true);
    }

    /** @param list<string> $arguments */
    private function moderatorResponse(int $updateId, string $command, array $arguments, int $moderatorTelegramId, array $message): string
    {
        return match ($command) {
            '/start' => 'Telegram-модератор подключён. Используйте /help для списка команд.',
            '/help' => "/start — состояние подключения\n/help — список команд\n/question — пометить ответное сообщение как вопрос\n/attach [queue_id] — добавить сообщение и фото к вопросу; без ID используется последний вопрос автора\n/status — состояние webhook\n/mute <user_id> [минуты]\n/unmute <user_id>\n/block <user_id>\n/unblock <user_id>",
            '/status' => 'Webhook активен. Обновления принимаются и сохраняются в Laravel.',
            '/question' => $this->markReplyAsQuestion($updateId, $message),
            '/attach' => $this->attachReply($arguments, $message, $moderatorTelegramId),
            '/mute', '/unmute', '/block', '/unblock' => $this->moderation->execute($command, $arguments, $moderatorTelegramId),
        };
    }

    /** @param array<string, mixed> $message */
    private function markReplyAsQuestion(int $updateId, array $message): string
    {
        $reply = $message['reply_to_message'] ?? null;
        if (! is_array($reply)) {
            return 'Ответьте командой /question на сообщение, которое нужно передать на модерацию.';
        }

        $authorId = (int) data_get($reply, 'from.id');
        $maxPending = max(1, (int) config('services.telegram.max_pending_per_user', 5));
        if ($authorId > 0 && DB::table('telegram_question_messages')
            ->where('telegram_user_id', $authorId)
            ->where('status', 'pending_moderation')
            ->count() >= $maxPending) {
            return "У автора уже есть {$maxPending} вопрос(ов) на модерации.";
        }

        $payload = ['message' => $reply];
        $mediaGroupId = (string) ($reply['media_group_id'] ?? '');
        if ($mediaGroupId !== '') {
            $albumPhotos = $this->albumPhotos($reply, $mediaGroupId);
            if ($albumPhotos !== []) {
                $payload['message']['album_photos'] = $albumPhotos;
            }
        }
        if (! $this->questionIntake->store($updateId, $payload, true)) {
            return 'Это сообщение уже передано на модерацию или не содержит текста.';
        }

        $this->questionModeration->preview($updateId, $payload, false);

        return 'Сообщение передано на модерацию.';
    }

    /** @param list<string> $arguments  @param array<string, mixed> $message */
    private function attachReply(array $arguments, array $message, int $moderatorTelegramId): string
    {
        $queueId = (int) ($arguments[0] ?? 0);
        $reply = $message['reply_to_message'] ?? null;
        if (! is_array($reply)) {
            return 'Ответьте командой /attach [queue_id] на сообщение с текстом или фото.';
        }

        if ($queueId < 1) {
            $authorId = (int) data_get($reply, 'from.id');
            $queueId = (int) DB::table('telegram_question_messages')
                ->where('telegram_chat_id', (int) ($message['chat']['id'] ?? 0))
                ->where('telegram_user_id', $authorId)
                ->where('status', 'pending_moderation')
                ->latest('id')
                ->value('id');

            if ($queueId < 1) {
                return 'У автора нет активного вопроса на модерации. Укажите queue_id явно.';
            }
        }

        $payload = ['message' => $reply];
        $mediaGroupId = (string) ($reply['media_group_id'] ?? '');
        if ($mediaGroupId !== '') {
            $albumPhotos = $this->albumPhotos($reply, $mediaGroupId);
            if ($albumPhotos !== []) {
                $payload['message']['album_photos'] = $albumPhotos;
            }
        }

        return $this->questionModeration->attach($queueId, $payload, $moderatorTelegramId);
    }

    /** @return list<array<int, array<string, mixed>>> */
    private function albumPhotos(array $reply, string $mediaGroupId): array
    {
        $groups = [];
        $seen = [];

        $add = static function (mixed $photos) use (&$groups, &$seen): void {
            if (! is_array($photos) || $photos === []) {
                return;
            }

            $largest = end($photos);
            $key = (string) data_get($largest, 'file_unique_id', data_get($largest, 'file_id', ''));
            if ($key === '' || isset($seen[$key])) {
                return;
            }

            $seen[$key] = true;
            $groups[] = $photos;
        };

        $add($reply['photo'] ?? null);

        foreach (['telegram_webhook_updates', 'telegram_question_messages'] as $table) {
            DB::table($table)
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.message.media_group_id')) = ?", [$mediaGroupId])
                ->get(['payload'])
                ->each(function (object $row) use ($add): void {
                    $add(data_get(json_decode((string) $row->payload, true), 'message.photo'));
                });
        }

        return $groups;
    }

    private function markProcessed(int $updateId): void
    {
        DB::table('telegram_webhook_updates')->where('update_id', $updateId)->update(['processed_at' => now()]);
    }
}
