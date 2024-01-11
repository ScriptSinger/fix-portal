<?php

namespace App\Traits;

use Carbon\Carbon;

trait DateTrait
{
    public function getDateAsCarbonAttribute()
    {
        return Carbon::parse($this->created_at);
    }
}
