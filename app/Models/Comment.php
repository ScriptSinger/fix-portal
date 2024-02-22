<?php

namespace App\Models;

use App\Traits\DateTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes, DateTrait;

    protected $fillable = [
        'text',
        'photos',
        'user_id',
    ];

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

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function dislikes()
    {
        return $this->morphMany(Dislike::class, 'dislikeable');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($comment) {
            $comment->replies()->delete();
        });

        static::restoring(function ($comment) {
            $comment->replies()->restore();
        });
    }
}
