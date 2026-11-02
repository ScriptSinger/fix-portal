<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
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

            if ($this->questionIntake->store($updateId, $payload)) {
                $this->questionModeration->preview($updateId, $payload);
            }
            $this->markProcessed($updateId);
            return;
        }

        $telegramUserId = (int) ($message['from']['id'] ?? 0);
        $response = $this->isModerator($telegramUserId)
            ? $this->moderatorResponse($updateId, $parsed[0], $parsed[1], $telegramUserId, $message)
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
            '/help' => "/start — состояние подключения\n/help — список команд\n/question — пометить ответное сообщение как вопрос\n/attach <queue_id> — добавить сообщение и фото к опубликованному вопросу\n/status — состояние webhook\n/mute <user_id> [минуты]\n/unmute <user_id>\n/block <user_id>\n/unblock <user_id>",
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
        if ($queueId < 1 || ! is_array($reply)) {
            return 'Ответьте /attach <queue_id> на сообщение с текстом или фото.';
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
