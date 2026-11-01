<?php

namespace App\Models;

use App\Services\ContentImageCleanupService;
use App\Enums\PostStatus;
use App\Traits\DateTrait;
use App\Traits\ImageHandlerTrait;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Post extends Model
{
    use DateTrait, HasFactory, ImageHandlerTrait, Sluggable;

    protected $fillable = [
        'title',
        'status',
        'description',
        'content',
        'category_id',
        'thumbnail',
        'administrator_id',

    ];

    protected $casts = [
        'status' => PostStatus::class,
    ];

    protected array $contentImagesForCleanup = [];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PostStatus::Published);
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
            ],
        ];
    }

    public function administrator(): BelongsTo
    {
        return $this->belongsTo(Administrator::class);
    }

    public function thumbnail(): HasOne
    {
        return $this->hasOne(Thumbnail::class);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail?->blog;
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function ctas(): HasMany
    {
        return $this->hasMany(Cta::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::updating(function (Post $post): void {
            $post->contentImagesForCleanup = app(ContentImageCleanupService::class)->urlsFromHtml(
                (string) $post->getOriginal('content').' '.(string) $post->getOriginal('description'),
            );
        });

        static::deleting(function ($post) {
            $post->contentImagesForCleanup = app(ContentImageCleanupService::class)->urlsFromHtml(
                (string) $post->content.' '.(string) $post->description,
            );
            $post->comments()->get()->each->delete();
            $post->thumbnail?->delete();
        });

        static::updated(fn (Post $post) => app(ContentImageCleanupService::class)->cleanup($post->contentImagesForCleanup));
        static::deleted(fn (Post $post) => app(ContentImageCleanupService::class)->cleanup($post->contentImagesForCleanup));
    }

    // public function getThumbnailAttribute($value)
    // {
    //     return Storage::url($value);
    // }
}
