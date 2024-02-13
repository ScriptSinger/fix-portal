<?php

namespace App\Models;

use App\Traits\DateTrait;
use App\Traits\ImageHandlerTrait;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory;


    use HasFactory, Sluggable, SoftDeletes, DateTrait, ImageHandlerTrait;

    protected $fillable = [
        'title',
        'description',
        'appliance_id',
        'photos',
        'user_id'
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function appliance()
    {
        return $this->belongsTo(Appliance::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }




    public function getSrcFromContentAttribute()
    {
        $html = $this->attributes['description'];
        return $this->getFirstSrc($html);
    }

    private function getFirstSrc($html)
    {
        $pattern = '/<img.+?src="(.+?)".*?>/';
        preg_match($pattern, $html, $matches);
        return isset($matches[1]) ? $matches[1] : null;
    }
}
