<?php

namespace App\Models;

use App\Services\ImageCleanupService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends Model
{
    use HasFactory, SoftDeletes;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function administrator(): BelongsTo
    {
        return $this->belongsTo(Administrator::class);
    }

    protected static function booted(): void
    {
        static::forceDeleted(fn (Image $image) => app(ImageCleanupService::class)->deleteFile($image->url));
    }

    public function getSizeMbAttribute()
    {
        $sizeInMb = round($this->attributes['size'] / 1048576, 2); // Разделение на 1024^2 для преобразования в мегабайты

        return $sizeInMb.' MB'; // Добавляем "MB" к значению
    }
}
