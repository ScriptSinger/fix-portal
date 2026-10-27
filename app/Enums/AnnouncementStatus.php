<?php

namespace App\Enums;

/** Состояние отправки вопроса в канал. Подпись и цвет бейджа берёт админка (поле Enum). */
enum AnnouncementStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';

    public function toString(): string
    {
        return match ($this) {
            self::Pending => 'В очереди',
            self::Sent => 'Отправлено',
            self::Failed => 'Не удалось',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Sent => 'success',
            self::Failed => 'error',
        };
    }
}
