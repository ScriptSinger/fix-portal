<?php

namespace App\Models;

use App\Services\ContentImageCleanupService;
use App\Services\ImageCleanupService;
use App\Traits\DateTrait;
use App\Traits\HasContentImages;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Reply extends Model
{
    use DateTrait, HasFactory;
    use HasContentImages;

    protected $fillable = [
        'text',
        'user_id',
        'comment_id',
        'quote_reply_id',
        'user_id',
    ];

    protected array $imageIdsForCleanup = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }

    protected static function boot()
    {
        parent::boot();

        static::updated(function (Reply $reply): void {
            if ($reply->wasChanged('text')) {
                $cleanup = app(ImageCleanupService::class);
                $cleanup->cleanup($cleanup->idsFromHtml($reply->getRawOriginal('text')));
                $contentCleanup = app(ContentImageCleanupService::class);
                $contentCleanup->cleanup($contentCleanup->urlsFromHtml($reply->getRawOriginal('text')));
            }
        });

        static::deleting(function ($reply) {
            $reply->imageIdsForCleanup = app(ImageCleanupService::class)->idsFromHtml($reply->text);
            $reply->reactions()->delete();
        });
        static::deleted(fn (Reply $reply) => app(ImageCleanupService::class)->cleanup($reply->imageIdsForCleanup));
        static::deleted(function (Reply $reply): void {
            $cleanup = app(ContentImageCleanupService::class);
            $cleanup->cleanup($cleanup->urlsFromHtml($reply->text));
        });
    }
}
