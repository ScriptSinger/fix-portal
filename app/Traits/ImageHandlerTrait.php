<?php

namespace App\Traits;

trait ImageHandlerTrait
{

    public function getImage($attributeName)
    {

        return asset("storage/{$this->$attributeName}");
    }

    public function getImages($attributeName = null)
    {
        return json_decode($this->$attributeName);
    }
}
