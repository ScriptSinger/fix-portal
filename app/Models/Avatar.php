<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Avatar extends Model
{
    use HasFactory;

    protected $fillable = [
        'uri',
        'mime',
        'size',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::deleted(function (Avatar $avatar): void {
            $path = $avatar->uri;
            $avatar->getConnection()->afterCommit(fn () => Storage::disk('public')->delete($path));
        });
    }
}
