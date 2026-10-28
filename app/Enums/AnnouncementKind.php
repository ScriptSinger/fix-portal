<?php

namespace App\Enums;

/** Вид отправки вопроса в канал: само объявление о новом вопросе или напоминание о нём. */
enum AnnouncementKind: string
{
    case New = 'new';
    case Reminder = 'reminder';

    public function toString(): string
    {
        return match ($this) {
            self::New => 'Новый вопрос',
            self::Reminder => 'Напоминание',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::New => 'info',
            self::Reminder => 'secondary',
        };
    }
}
