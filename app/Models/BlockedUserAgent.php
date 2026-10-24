<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlockedUserAgent extends Model
{
    use HasFactory, SoftDeletes;


    protected $fillable = [
        'user_agent'
    ];

    /**
     * Списки блокировок кэшируются в BlockedMiddleware, поэтому сбрасываем кэш при любом изменении.
     */
    protected static function booted(): void
    {
        $forget = static fn () => \Illuminate\Support\Facades\Cache::forget('blocked.user_agents');

        static::saved($forget);
        static::deleted($forget);
        static::restored($forget);
    }
}
