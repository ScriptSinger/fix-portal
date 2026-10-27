<?php

namespace App\Models;

use App\Traits\DateTrait;
use App\Traits\ImageHandlerTrait;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

use Intervention\Image\Laravel\Facades\Image;

class Post extends Model
{
    use HasFactory, Sluggable, DateTrait, ImageHandlerTrait;

    protected $fillable = [
        'title',
        'description',
        'content',
        'category_id',
        'thumbnail',
        'administrator_id'

    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
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

        static::deleting(function ($post) {
            $post->comments()->get()->each->delete();
        });
    }

    // public function getThumbnailAttribute($value)
    // {
    //     return Storage::url($value);
    // }
}
