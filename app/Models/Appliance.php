<?php

namespace App\Models;

use App\Traits\DateTrait;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appliance extends Model
{
    use HasFactory, Sluggable, DateTrait;

    protected $fillable = ['title'];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
