<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Единственная строка настроек сайта, которые админ меняет из панели.
 */
class SiteSetting extends Model
{
    private const ADS_CACHE_KEY = 'site_settings.ads_enabled';

    protected $fillable = ['ads_enabled'];

    protected $casts = ['ads_enabled' => 'boolean'];

    public static function adsEnabled(): bool
    {
        return Cache::remember(self::ADS_CACHE_KEY, 300, fn () => (bool) static::query()->value('ads_enabled'));
    }

    protected static function booted(): void
    {
        // Ползунок в админке должен срабатывать сразу, а не через время жизни кэша
        static::saved(fn () => Cache::forget(self::ADS_CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::ADS_CACHE_KEY));
    }
}
