<?php

namespace App\Traits;

use Carbon\Carbon;

trait DateTrait
{
    public function getCreatedDate()
    {
        if ($this->created_at) {
            return Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->format('d F, Y');
        } else {
            return 'N/A'; // или другое значение по умолчанию для случая отсутствия даты
        }
    }

    public function getDeletedDate()
    {
        if ($this->deleted_at) {
            return Carbon::createFromFormat('Y-m-d H:i:s', $this->deleted_at)->format('d F, Y');
        } else {
            return 'N/A';
        }
    }

    public function getEmailVerifiedDate()
    {
        if ($this->email_verified_at) {
            return Carbon::createFromFormat('Y-m-d H:i:s', $this->email_verified_at)->format('d F, Y');
        } else {
            return 'N/A';
        }
    }
}
