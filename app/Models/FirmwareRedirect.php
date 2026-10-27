<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Старый адрес слитого дубля прошивки → основная запись. По нему страница дубля отдаёт постоянный редирект,
 * чтобы старые ссылки и поисковики вели на основную страницу.
 */
class FirmwareRedirect extends Model
{
    protected $primaryKey = 'old_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['old_id', 'new_id'];
}
