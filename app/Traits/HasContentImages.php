<?php

namespace App\Traits;

use App\Models\Image;
use App\Services\ImageRegistry;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasContentImages
{
    public function contentImages(): MorphToMany
    {
        return $this->morphToMany(Image::class, 'imageable')->withPivot('field');
    }

    public function save(array $options = [])
    {
        return $this->getConnection()->transaction(function () use ($options) {
            $saved = parent::save($options);
            if ($saved) {
                app(ImageRegistry::class)->sync($this);
            }

            return $saved;
        });
    }

    public function delete()
    {
        return $this->getConnection()->transaction(function () {
            $deleted = parent::delete();
            if ($deleted) {
                app(ImageRegistry::class)->detach($this);
            }

            return $deleted;
        });
    }
}
