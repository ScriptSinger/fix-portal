<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Автор статей. Вход в админку теперь через MoonShine (moonshine_users),
 * эта модель нужна только для имени автора у постов и изображений.
 */
class Administrator extends Model
{
    protected $fillable = [
        'name',
        'email',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }
}
