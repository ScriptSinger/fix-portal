<?php

namespace App\Models;

use App\Notifications\ActivityNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\DatabaseNotification;

/**
 * Письмо об активности, уже отправленное человеку. Отдельной таблицы нет: письмо — это уведомление кабинета,
 * у которого проставлено emailed_at, поэтому журнал писем в админке строится из тех же записей.
 */
class SentEmail extends DatabaseNotification
{
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'emailed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('emailed', fn (Builder $query) => $query->whereNotNull('emailed_at'));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'notifiable_id');
    }

    /** Что случилось: ответили на вопрос или ответ отмечен решением. */
    public function kindLabel(): string
    {
        return match ($this->data['kind'] ?? null) {
            ActivityNotification::ANSWER => 'Ответили на вопрос',
            ActivityNotification::SOLUTION => 'Ответ отмечен решением',
            default => (string) ($this->data['kind'] ?? ''),
        };
    }
}
