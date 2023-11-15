<?php

namespace App\Traits;

trait ModelImageTrait
{
    public function getImage($attributeValue = null, $defaultPath = "assets/images/no-image.jpg")
    {
        if ($this->$attributeValue == null) {
            return asset($defaultPath);
        }
        return asset("storage/{$this->$attributeValue}");
    }
}
