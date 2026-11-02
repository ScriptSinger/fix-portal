<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramQuestionMessage extends Model
{
    protected $fillable = [
        'telegram_chat_id',
        'telegram_message_id',
        'telegram_update_id',
        'telegram_user_id',
        'text',
        'payload',
        'status',
        'question_id',
        'appliance_id',
        'moderation_chat_id',
        'moderation_message_id',
        'error_message',
    ];

    protected $casts = ['payload' => 'array'];

    public function appliance(): BelongsTo
    {
        return $this->belongsTo(Appliance::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
