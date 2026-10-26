<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Связка «аккаунт в соцсети → пользователь сайта» (Яндекс ID, VK ID).
 */
class SocialAccount extends Model
{
    protected $fillable = ['user_id', 'provider', 'provider_user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
