<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramQuestionPublication extends Model
{
    protected $fillable = ['telegram_question_message_id', 'status', 'question_id', 'appliance_id', 'error_message'];

    public function telegramQuestion(): BelongsTo
    {
        return $this->belongsTo(TelegramQuestionMessage::class, 'telegram_question_message_id');
    }
}
