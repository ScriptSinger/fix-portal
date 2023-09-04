<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use HasFactory;
    use Sluggable;
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


    public static function uploadThumbnail(Request $request, $thumbnail = null)
    {
        if ($request->hasFile('thumbnail')) {
            // Если файл был загружен, удалить старую миниатюру, если путь к ней существует
            if ($thumbnail) {
                Storage::disk('public')->delete($thumbnail);
            }
            $folder = date('Y-m-d');
            return $request->file('thumbnail')->store("thumbnails/$folder", 'public');
        } elseif ($thumbnail) {
            // Если файл не был загружен, но существует путь к старой миниатюре,
            // вернуть её путь
            return $thumbnail;
        }
        // Если файл не был загружен и пути к старой миниатюре не существует,
        // вернуть null
        return null;
    }

    public function getImage()
    {
        if (!$this->thumbnail) {
            return asset("assets/images/no-image.jpg");
        }
        return asset("storage/{$this->thumbnail}");
    }
}
