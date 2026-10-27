<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Отправка вопроса в канал (Telegram-чат мастеров, позже VK): одна запись на пару «вопрос, канал».
 */
class QuestionAnnouncement extends Model
{
    public const PENDING = 'pending';
    public const SENT = 'sent';
    public const FAILED = 'failed';

    protected $fillable = ['question_id', 'channel', 'status', 'attempts', 'last_error', 'external_id', 'sent_at'];

    protected $casts = ['sent_at' => 'datetime'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class)->withTrashed();
    }
}
