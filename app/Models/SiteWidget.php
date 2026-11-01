<?php

namespace App\Models;

use App\Services\LatestDiscussion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteWidget extends Model
{
    private const CACHE_KEY = 'site_widgets.config.v1';

    protected $fillable = [
        'key',
        'title',
        'description',
        'enabled',
        'block_id',
        'url',
        'display_limit',
        'sort',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'display_limit' => 'integer',
        'sort' => 'integer',
    ];

    public static function enabled(string $key): bool
    {
        return (bool) static::value($key, 'enabled', $key !== 'ads');
    }

    public static function value(string $key, string $attribute, mixed $default = null): mixed
    {
        $widgets = Cache::remember(self::CACHE_KEY, 300, static fn (): array => static::query()
            ->get()
            ->keyBy('key')
            ->map(static fn (self $widget): array => $widget->toArray())
            ->all());

        return $widgets[$key][$attribute] ?? $default;
    }

    protected static function booted(): void
    {
        static::saved(function (): void {
            Cache::forget(self::CACHE_KEY);
            Cache::forget(LatestDiscussion::CACHE_KEY);
        });
        static::deleted(function (): void {
            Cache::forget(self::CACHE_KEY);
            Cache::forget(LatestDiscussion::CACHE_KEY);
        });
    }
}
