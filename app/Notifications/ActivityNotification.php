<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Событие в обсуждении для личного кабинета: ответ на вопрос, ответ на сообщение, реакция.
 * Данные хранятся готовыми к показу, чтобы лента не зависела от удалённых записей.
 */
class ActivityNotification extends Notification
{
    public const ANSWER = 'answer';
    public const REPLY = 'reply';
    public const REACTION = 'reaction';
    public const SOLUTION = 'solution';

    /** @param array{kind: string, actor_id: int, actor_name: string, title: string, in: string, url: string, snippet?: string, emoji?: string, target_id?: string} $data */
    public function __construct(private array $data)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return $this->data;
    }
}
