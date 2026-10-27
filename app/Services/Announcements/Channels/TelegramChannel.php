<?php

namespace App\Services\Announcements\Channels;

use App\Models\Question;
use App\Support\PlainText;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
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

    public function text(Question $question): string
    {
        $facts = array_filter([
            $question->appliance?->title,
            $question->model,
            $question->error_code ? 'ошибка ' . $question->error_code : null,
        ]);
        $author = $question->user?->name;

        return implode("\n\n", array_filter([
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
        $question->loadMissing(['appliance', 'user']);
        $token = (string) config('services.telegram.bot_token');
        $url = rtrim((string) config('services.telegram.api_url'), '/') . '/bot' . $token . '/sendMessage';

        try {
            $response = Http::timeout(15)
                ->withOptions(array_filter(['proxy' => config('services.telegram.proxy')]))
                ->post($url, [
                    'chat_id' => config('services.telegram.chat_id'),
                    'text' => $this->text($question),
                    'parse_mode' => 'HTML',
                    // Карточку ссылки строит краулер Telegram, а он до сайта не доходит: вместо неё кнопка
                    'link_preview_options' => ['is_disabled' => true],
                    'reply_markup' => ['inline_keyboard' => [[['text' => 'Открыть вопрос на сайте', 'url' => $this->url($question)]]]],
                ])
                ->throw();
        } catch (RequestException|ConnectionException $e) {
            // в тексте исключения Guzzle стоят адрес с токеном и логин с паролем прокси: наружу они не уходят
            throw new \RuntimeException('Telegram: ' . $this->redact($e->getMessage()));
        }

        $id = $response->json('result.message_id');

        return $id === null ? null : (string) $id;
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
