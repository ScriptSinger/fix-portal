<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Blocker extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
    ];

    /**
     * Списки блокировок кэшируются в BlockedMiddleware, поэтому сбрасываем кэш при любом изменении.
     */
    protected static function booted(): void
    {
        $forget = static fn () => Cache::forget('blocked.ips');

        static::saved($forget);
        static::deleted($forget);
    }
}
