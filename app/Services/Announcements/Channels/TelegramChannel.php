<?php

namespace App\Services\Announcements\Channels;

use App\Models\Question;
use App\Support\PlainText;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/** Сообщение в чат мастеров: заголовок, техника, начало описания и ссылка. Отвечают мастера на сайте. */
class TelegramChannel implements AnnouncementChannel
{
    public const NAME = 'telegram';

    private const EXCERPT = 600;

    public function name(): string
    {
        return self::NAME;
    }

    public function enabled(): bool
    {
        return filled(config('services.telegram.bot_token')) && filled(config('services.telegram.chat_id'));
    }

    public function canModify(): bool
    {
        return true;
    }

    public function text(Question $question): string
    {
        $facts = array_filter([
            $question->appliance?->title,
            $question->model,
            $question->error_code ? 'ошибка ' . $question->error_code : null,
        ]);
        $author = $question->user?->name;

        return implode("\n\n", array_filter([
            $question->isSolved() ? '✅ Вопрос решён' : null,
            '<b>' . $this->escape($question->title) . '</b>',
            implode("\n", array_filter([
                $facts ? $this->escape(implode(' · ', $facts)) : null,
                $author ? 'Спрашивает: ' . $this->escape($author) : null,
            ])),
            $this->escape(PlainText::from($question->description, self::EXCERPT)),
            $this->url($question),
        ]));
    }

    public function url(Question $question): string
    {
        return route('questions.show', ['question' => $question->slug]);
    }

    public function send(Question $question): ?string
    {
        return $this->messageId($this->call('sendMessage', $this->payload($question) + $this->thread()));
    }

    public function canRemind(): bool
    {
        return true;
    }

    /** Текст напоминания: просьба, а не требование, и без привязки к поломке (человек может искать прошивку). */
    public function reminderText(Question $question): string
    {
        return implode("\n\n", [
            'Этот вопрос пока без ответа. Если разбираетесь в теме, загляните, пожалуйста.',
            '<b>' . $this->escape($question->title) . '</b>',
            $this->url($question),
        ]);
    }

    public function remind(Question $question, ?string $originalId): ?string
    {
        $payload = [
            'chat_id' => config('services.telegram.chat_id'),
            'text' => $this->reminderText($question),
            'parse_mode' => 'HTML',
            'link_preview_options' => ['is_disabled' => true],
        ];

        // ответом на исходное сообщение: в чате видна связь; если его уже нет, напоминание уйдёт обычным
        if ($originalId) {
            $payload['reply_parameters'] = ['message_id' => (int) $originalId, 'allow_sending_without_reply' => true];
        }

        return $this->messageId($this->call('sendMessage', $payload + $this->thread()));
    }

    public function digestText(int $count, string $url): string
    {
        return "На сайте сейчас {$count} " . $this->questionWord($count) . " без ответа. Если будет время и желание, загляните: {$url}\nСпасибо всем, кто помогает!";
    }

    public function digest(int $count, string $url): ?string
    {
        return $this->messageId($this->call('sendMessage', [
            'chat_id' => config('services.telegram.chat_id'),
            'text' => $this->digestText($count, $url),
            'link_preview_options' => ['is_disabled' => true],
        ] + $this->thread()));
    }

    /** вопрос, вопроса, вопросов */
    private function questionWord(int $n): string
    {
        $last = $n % 10;
        $lastTwo = $n % 100;

        return match (true) {
            $last === 1 && $lastTwo !== 11 => 'вопрос',
            $last >= 2 && $last <= 4 && ($lastTwo < 10 || $lastTwo >= 20) => 'вопроса',
            default => 'вопросов',
        };
    }

    /**
     * Тема форум-группы нужна только при отправке: правка и удаление находят сообщение по его номеру.
     * Тема «Общая» имеет номер 1, но Telegram не принимает его в запросе (message thread not found): для неё номер не указывают.
     *
     * @return array<string, int>
     */
    private function thread(): array
    {
        $thread = (int) config('services.telegram.thread_id');

        return $thread > 1 ? ['message_thread_id' => $thread] : [];
    }

    private function messageId(Response $response): ?string
    {
        $id = $response->json('result.message_id');

        return $id === null ? null : (string) $id;
    }

    /** Меняет текст уже отправленного сообщения: автор поправил вопрос. */
    public function update(Question $question, string $externalId): void
    {
        $this->call('editMessageText', $this->payload($question) + ['message_id' => (int) $externalId], tolerate: [
            // текст не изменился (правка не затронула то, что показано в чате): это не сбой
            'message is not modified',
        ]);
    }

    /** Удаляет сообщение из чата: вопрос удалён, кнопка вела бы на несуществующую страницу. */
    public function retract(string $externalId): void
    {
        $this->call('deleteMessage', ['chat_id' => config('services.telegram.chat_id'), 'message_id' => (int) $externalId], tolerate: [
            'message to delete not found',
            "message can't be deleted",
        ]);
    }

    /** @return array<string, mixed> */
    private function payload(Question $question): array
    {
        $question->loadMissing(['appliance', 'user']);

        return [
            'chat_id' => config('services.telegram.chat_id'),
            'text' => $this->text($question),
            'parse_mode' => 'HTML',
            // Карточку ссылки строит краулер Telegram, а он до сайта не доходит: пустая карточка не нужна
            'link_preview_options' => ['is_disabled' => true],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<string>  $tolerate  части описания ошибки Telegram, которые считаются успехом
     */
    private function call(string $method, array $payload, array $tolerate = []): Response
    {
        $url = rtrim((string) config('services.telegram.api_url'), '/') . '/bot' . config('services.telegram.bot_token') . '/' . $method;

        try {
            return Http::timeout(15)
                ->withOptions(array_filter(['proxy' => config('services.telegram.proxy')]))
                ->post($url, $payload)
                ->throw();
        } catch (RequestException $e) {
            $description = (string) $e->response->json('description');

            foreach ($tolerate as $part) {
                if (str_contains($description, $part)) {
                    return $e->response;
                }
            }

            throw new \RuntimeException('Telegram: ' . $this->redact($e->getMessage()));
        } catch (ConnectionException $e) {
            // в тексте исключения Guzzle стоят адрес с токеном и логин с паролем прокси: наружу они не уходят
            throw new \RuntimeException('Telegram: ' . $this->redact($e->getMessage()));
        }
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function redact(string $message): string
    {
        $message = preg_replace('#/bot[^/\s]+/#', '/bot***/', $message);

        return preg_replace('#(://)[^/@\s]+@#', '$1***@', $message);
    }
}
