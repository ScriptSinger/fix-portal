<?php

namespace App\Services\Announcements\Channels;

use App\Models\Question;

interface AnnouncementChannel
{
    /** Ключ канала в таблице question_announcements. */
    public function name(): string;

    /** Настроен ли канал: пока нет, вопросы в него не ставятся. */
    public function enabled(): bool;

    /** Отправляет вопрос и возвращает внешний номер сообщения. При сбое бросает исключение без секретов в тексте. */
    public function send(Question $question): ?string;
}
