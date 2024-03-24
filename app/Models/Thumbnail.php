<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Thumbnail extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'original',
        'blog',
        'small'
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
