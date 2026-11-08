<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Thumbnail extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'original',
        'blog',
        'small',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    protected static function booted(): void
    {
        static::deleted(function (Thumbnail $thumbnail): void {
            foreach ([$thumbnail->original, $thumbnail->blog, $thumbnail->small] as $url) {
                $path = parse_url((string) $url, PHP_URL_PATH);
                $path = is_string($path) ? preg_replace('#^/?storage/#', '', $path) : null;

                if (is_string($path) && str_starts_with($path, 'images/posts/thumbnails/')) {
                    $thumbnail->getConnection()->afterCommit(fn () => Storage::disk('public')->delete($path));
                }
            }
        });
    }
}
