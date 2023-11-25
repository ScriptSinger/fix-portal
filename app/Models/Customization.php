<?php

namespace App\Models;

use App\Traits\ImageHandlerTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customization extends Model
{
    use HasFactory, ImageHandlerTrait;

    protected $fillable = [
        'favicon',
        'logo',
        'banner',
        'title',
        'description',
        'copyright',
    ];
}
