<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'route',
        'ipAddress',
        'userAgent',
        'locale',
        'referer',
        'methodType',
        'user_id',
        'location'
    ];

    // Если вам нужно получать данные о пользователе, можно определить отношение в модели
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
