<?php

namespace App\Models;

use App\Traits\DateTrait;
use App\Traits\ImageHandlerTrait;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use Intervention\Image\Laravel\Facades\Image;

class Post extends Model
{
    use HasFactory, Sluggable, SoftDeletes, DateTrait, ImageHandlerTrait;

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

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($post) {
            $post->comments()->each(function ($comment) {
                $comment->replies()->delete();
                $comment->likes()->delete();
                $comment->dislikes()->delete();
            });
            $post->comments()->delete();
        });

        static::restoring(function ($post) {
            $post->comments()->withTrashed()->each(function ($comment) {
                $comment->replies()->withTrashed()->restore();
                $comment->likes()->withTrashed()->restore();
                $comment->dislikes()->withTrashed()->restore();
            });
            $post->comments()->withTrashed()->restore();
        });
    }

    // public function getThumbnailAttribute($value)
    // {
    //     return Storage::url($value);
    // }
}
