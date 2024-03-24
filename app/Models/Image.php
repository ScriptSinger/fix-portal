<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Image extends Model
{

    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function administrator(): BelongsTo
    {
        return $this->belongsTo(Administrator::class);
    }

    public function getSizeMbAttribute()
    {
        $sizeInMb = round($this->attributes['size'] / 1048576, 2); // Разделение на 1024^2 для преобразования в мегабайты
        return $sizeInMb . " MB"; // Добавляем "MB" к значению
    }
}
