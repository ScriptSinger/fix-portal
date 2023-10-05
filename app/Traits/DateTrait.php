<?php

namespace App\Traits;

use Carbon\Carbon;

trait DateTrait
{
    public function getCreatedDate()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->format('d F, Y');
    }
    public function getDeletedDate()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->deleted_at)->format('d F, Y');
    }
}
