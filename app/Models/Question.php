<?php

namespace App\Models;

use App\Traits\DateTrait;
use App\Traits\ImageHandlerTrait;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Question extends Model
{
    use HasFactory;


    use HasFactory, Sluggable, DateTrait, ImageHandlerTrait;

    protected $fillable = [
        'title',
        'description',
        'appliance_id',
        'model',
        'error_code',
        'photos',
        'user_id',
        'solved_at',
        'solution_type',
        'solution_id',
    ];

    protected $casts = ['solved_at' => 'datetime'];

    /** Отметку «решён» ведём с этой даты: у старых вопросов статуса нет, и «без решения» к ним не относится. */
    public const SOLUTION_TRACKING_SINCE = '2026-10-26';

    public function isSolved(): bool
    {
        return $this->solved_at !== null;
    }

    /** Ответ, который автор отметил решением (комментарий или ответ), либо null. */
    public function solution(): Comment|Reply|null
    {
        return match ($this->solution_type) {
            'comment' => Comment::find($this->solution_id),
            'reply' => Reply::find($this->solution_id),
            default => null,
        };
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function appliance(): BelongsTo
    {
        return $this->belongsTo(Appliance::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(QuestionAnnouncement::class);
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

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($question) {
            $question->comments()->get()->each->delete();
        });
    }
}
