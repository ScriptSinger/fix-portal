<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Firmware extends Model
{

    protected $fillable = [
        'title',
        'path_id',
        'size',
        'date',
        'extension',
        'platform',
        'crc32',
        'data'
    ];
}
