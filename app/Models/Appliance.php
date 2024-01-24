<?php

namespace App\Models;

use App\Traits\DateTrait;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appliance extends Model
{
    use HasFactory, Sluggable, DateTrait;
    // use SoftDeletes;
    protected $fillable = ['title'];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
