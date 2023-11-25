<?php

namespace App\Traits;

trait ImageHandlerTrait
{

    public function getImage($attributeName = null, $defaultPath = "assets/images/no-image.jpg")
    {

        if ($this->$attributeName == null) {
            return asset($defaultPath);
        }

        return asset("storage/{$this->$attributeName}");
    }
}
