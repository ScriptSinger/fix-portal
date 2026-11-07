<?php

namespace App\Enums;

enum TelegramQuestionResolutionStatus: string
{
    case Open = 'open';
    case Solved = 'solved';

    public function toString(): string
    {
        return match ($this) {
            self::Open => 'Открыт',
            self::Solved => 'Решён',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Open => 'warning',
            self::Solved => 'success',
        };
    }
}
