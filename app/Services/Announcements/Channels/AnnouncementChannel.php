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

    /** Можно ли менять и удалять отправленное. Если нет, правка и удаление вопроса канал не трогают. */
    public function canModify(): bool;

    /** Обновляет уже отправленное сообщение (вопрос отредактировали). Бросает исключение без секретов в тексте. */
    public function update(Question $question, string $externalId): void;

    /** Удаляет отправленное сообщение (вопрос удалили). Сообщение, которого уже нет, не считается ошибкой. */
    public function retract(string $externalId): void;
}
