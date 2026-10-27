<?php

namespace App\Services\Announcements\Channels;

use App\Models\Question;
use App\Support\PlainText;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

/** Запись на стене VK-группы от имени сообщества: заголовок, техника, начало описания; ссылка идёт вложением, VK сам строит карточку. */
class VkChannel implements AnnouncementChannel
{
    public const NAME = 'vk';

    private const EXCERPT = 800;

    private const API = 'https://api.vk.com/method/';

    public function name(): string
    {
        return self::NAME;
    }

    public function enabled(): bool
    {
        return filled(config('services.vk_group.token')) && filled(config('services.vk_group.group_id'));
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
            $question->title,
            implode("\n", array_filter([
                $facts ? implode(' · ', $facts) : null,
                $author ? 'Спрашивает: ' . $author : null,
            ])),
            PlainText::from($question->description, self::EXCERPT),
        ]));
    }

    public function url(Question $question): string
    {
        return route('questions.show', ['question' => $question->slug]);
    }

    public function send(Question $question): ?string
    {
        $response = $this->call('wall.post', ['from_group' => 1] + $this->content($question));

        $id = $response['post_id'] ?? null;

        return $id === null ? null : (string) $id;
    }

    /** Меняет уже опубликованную запись: автор поправил вопрос или отметил его решённым. */
    public function update(Question $question, string $externalId): void
    {
        $this->call('wall.edit', ['post_id' => (int) $externalId] + $this->content($question));
    }

    /** Удаляет запись со стены: вопрос удалён. */
    public function retract(string $externalId): void
    {
        $this->call('wall.delete', ['post_id' => (int) $externalId]);
    }

    /** @return array<string, mixed> */
    private function content(Question $question): array
    {
        $question->loadMissing(['appliance', 'user']);

        return ['message' => $this->text($question), 'attachments' => $this->url($question)];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed> поле response ответа VK
     */
    private function call(string $method, array $params): array
    {
        $params += ['owner_id' => -(int) config('services.vk_group.group_id'), 'v' => config('services.vk_group.version')];

        try {
            // токен в заголовке, а не в адресе: в тексте исключений и журналах его не будет
            $body = Http::timeout(15)->withToken((string) config('services.vk_group.token'))->asForm()->post(self::API . $method, $params)->throw()->json();
        } catch (RequestException|ConnectionException $e) {
            throw new \RuntimeException('VK: ' . $this->redact($e->getMessage()));
        }

        // ошибки VK приходят с кодом 200 внутри ответа
        if (isset($body['error'])) {
            throw new \RuntimeException('VK: ' . $this->redact((string) ($body['error']['error_msg'] ?? 'ошибка')) . ' (код ' . ($body['error']['error_code'] ?? '?') . ')');
        }

        $response = $body['response'] ?? [];

        return is_array($response) ? $response : [];
    }

    private function redact(string $message): string
    {
        return preg_replace('#(access_token=|Bearer\s+)[^&\s"\']+#i', '$1***', $message);
    }
}
