<?php

namespace App\Services\Announcements\Channels;

use App\Models\Question;
use App\Support\PlainText;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

/** Запись на стене VK-группы от имени сообщества: заголовок, техника, начало описания и ссылка в тексте. */
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

    /** Ключ сообщества не может править и удалять записи (wall.edit и wall.delete отвечают кодом 27), только публиковать. */
    public function canModify(): bool
    {
        return false;
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
            $question->title,
            implode("\n", array_filter([
                $facts ? implode(' · ', $facts) : null,
                $author ? 'Спрашивает: ' . $author : null,
            ])),
            PlainText::from($question->description, self::EXCERPT),
            // Ссылкой-вложением VK требует картинку определённого размера со страницы (link_photo_sizing_rule) и иначе отказывает во всей записи
            $this->url($question),
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

    /** VK не даёт ключу сообщества править записи: правка вопроса на стене не отражается (см. canModify). */
    public function update(Question $question, string $externalId): void
    {
    }

    /** VK не даёт ключу сообщества удалять записи: при удалении вопроса запись остаётся на стене (см. canModify). */
    public function retract(string $externalId): void
    {
    }

    /** @return array<string, mixed> */
    private function content(Question $question): array
    {
        $question->loadMissing(['appliance', 'user']);

        return ['message' => $this->text($question)];
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
