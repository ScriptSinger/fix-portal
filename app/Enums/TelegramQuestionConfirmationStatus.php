<?php

namespace App\Enums;

enum TelegramQuestionConfirmationStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Declined = 'declined';
    case Expired = 'expired';
}
