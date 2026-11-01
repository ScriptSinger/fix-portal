<?php

namespace App\Models;

use App\Services\ImageCleanupService;
use App\Traits\DateTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    use DateTrait, HasFactory;

    protected $fillable = [
        'text',
        'photos',
        'user_id',
    ];

    protected array $imageIdsForCleanup = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class);
    }

    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($comment) {
            $comment->imageIdsForCleanup = app(ImageCleanupService::class)->idsFromHtml($comment->text);
            $comment->replies()->get()->each->delete();
            $comment->reactions()->delete();
        });
        static::deleted(fn (Comment $comment) => app(ImageCleanupService::class)->cleanup($comment->imageIdsForCleanup));
    }
}
