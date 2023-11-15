<?php

namespace App\Models;

use Carbon\Carbon;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FileUploadTrait;
use App\Traits\ModelImageTrait;

class Post extends Model
{
    use HasFactory, Sluggable, FileUploadTrait, ModelImageTrait;

    //массовое присваивание
    protected $fillable = [
        'title',
        'description',
        'content',
        'category_id',
        'thumbnail'

    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function postComment()
    {
        return $this->hasMany(PostComment::class);
    }

    public function getPostDate()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->format('d F, Y');
    }
}
