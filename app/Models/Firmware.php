<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Firmware extends Model
{
    use  SoftDeletes;

    protected $fillable = [
        'title',
        'path_id',
        'size',
        'date',
        'extension',
        'platform',
        'crc32',
        'data'
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function getModelNameAttribute(): ?string
    {
        if (!$this->data) {
            return null;
        }

        if (preg_match('/<td>\\s*Модель\\s*<\\/td>\\s*<td>(.*?)<\\/td>/ui', $this->data, $matches)) {
            return strip_tags($matches[1]);
        }

        return null;
    }

    public function getSerialNumberAttribute(): ?string
    {
        if (!$this->data) {
            return null;
        }

        if (preg_match('/<td>\\s*S\\/?N\\s*<\\/td>\\s*<td>(.*?)<\\/td>/ui', $this->data, $matches)) {
            return strip_tags($matches[1]);
        }

        return null;
    }

    public function getMetaTitleAttribute(): string
    {
        if ($this->model_name && $this->serial_number) {
            return $this->model_name . ' | S/N ' . $this->serial_number;
        }
        if ($this->model_name) {
            return $this->model_name . ' | ID ' . $this->id;
        }
        return 'Прошивка: ' . $this->title . ' | ID ' . $this->id;
    }

    public function getMetaDescriptionAttribute(): string
    {
        $parts = [
            'Прошивка ' . $this->title,
        ];

        if ($this->model_name) {
            $parts[] = 'Модель: ' . $this->model_name;
        }
        if ($this->platform) {
            $parts[] = 'Платформа: ' . $this->platform;
        }
        if ($this->extension) {
            $parts[] = 'Формат: ' . $this->extension;
        }

        $text = implode('. ', $parts) . '.';
        return mb_substr($text, 0, 160);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($firmware) {
            $firmware->comments()->each(function ($comment) {
                $comment->replies()->delete();
            });
            $firmware->comments()->delete();
        });

        static::restoring(function ($firmware) {
            $firmware->comments()->withTrashed()->each(function ($comment) {
                $comment->replies()->withTrashed()->restore();
            });
            $firmware->comments()->withTrashed()->restore();
        });
    }
}
