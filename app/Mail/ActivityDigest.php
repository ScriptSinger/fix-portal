<?php

namespace App\Mail;

use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Support\Str;

/**
 * Письмо о том, что человеку ответили на вопрос или его ответ отмечен решением. Отправляется из задачи очереди,
 * поэтому само в очередь не ставится. Данные берутся из уведомлений кабинета (готовые к показу).
 */
class ActivityDigest extends Mailable
{
    /** @param list<array{kind: string, actor_name: string, title: string, in: string, url: string, snippet?: string}> $items */
    public function __construct(public User $user, public array $items)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine());
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.activity',
            text: 'emails.activity-text',
            with: [
                'user' => $this->user,
                'items' => $this->items,
                'unsubscribeUrl' => $this->unsubscribeUrl(),
                'profileUrl' => route('profile.edit'),
            ],
        );
    }

    /** Отключение письма без входа: почтовые программы показывают кнопку «Отписаться», Gmail и Яндекс требуют отписку в один клик. */
    public function headers(): Headers
    {
        return new Headers(text: [
            'List-Unsubscribe' => '<' . $this->unsubscribeUrl() . '>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
    }

    public function unsubscribeUrl(): string
    {
        return route('email.unsubscribe.show', ['token' => $this->user->unsubscribeToken()]);
    }

    private function subjectLine(): string
    {
        $count = count($this->items);
        $kinds = array_unique(array_column($this->items, 'kind'));
        $titles = array_unique(array_column($this->items, 'title'));
        $title = Str::limit((string) ($this->items[0]['title'] ?? ''), 80);

        return match (true) {
            $count === 1 && $kinds === [ActivityNotification::SOLUTION] => "Ваш ответ отмечен решением: {$title}",
            $count === 1 => "Вам ответили на вопрос: {$title}",
            $kinds === [ActivityNotification::ANSWER] && count($titles) === 1 => "Новые ответы на ваш вопрос: {$title} ({$count})",
            $kinds === [ActivityNotification::ANSWER] => "Новые ответы на ваши вопросы ({$count})",
            $kinds === [ActivityNotification::SOLUTION] => "Ваши ответы отмечены решением ({$count})",
            default => "Новое на " . config('app.name', 'Ufamasters') . " ({$count})",
        };
    }
}
