<?php

namespace App\Models;

use App\Traits\FileUploadTrait;
use App\Traits\ModelImageTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customization extends Model
{
    use HasFactory, FileUploadTrait, ModelImageTrait;

    protected $fillable = [
        'favicon',
        'logo',
        'banner',
        'title',
        'description',
        'copyright',
    ];
}
