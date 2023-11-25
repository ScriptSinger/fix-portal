<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class FileUploader
{
    private $data;
    private $model;

    public static function getInstance($data)
    {
        return new self($data);
    }

    private function __construct($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }


    public function removePrev()
    {
        if ($this->model) {
            foreach ($this->data as $key => $value) {
                if (file_exists($value)) {
                    $fileLocation = $this->model->$key;

                    if ($fileLocation !== null) {

                        Storage::disk('public')->delete($this->model->$key);
                    }
                }
            }
        }
        return $this;
    }

    private function makeDir($dir, $subDir = null)
    {
        $dir = $subDir ? "$dir/$subDir" : $dir;
        Storage::disk('public')->makeDirectory($dir);
        // File::makeDirectory(storage_path("app/public/$dir"), 0755, true, true);
        return $dir;
    }

    public function save($subDir = null)
    {
        foreach ($this->data as $key => $value) {
            if (file_exists($value)) {
                $dir = $this->makeDir($key, $subDir);
                $this->data[$key] = $value->store($dir, 'public');
            }
        }
        return $this;
    }

    public function resizeSave($width = null, $height = null)
    {
        $width = $width ?? 200;
        $height = $height ?? 200;

        foreach ($this->data as $key => $value) {
            if (file_exists($value)) {

                $path = storage_path("app/public/$key/" . $value->hashName());
                Image::make($value->path())
                    ->resize($width, $height)
                    ->save($path);

                $this->data[$key] = $key . "/" . $value->hashName();
            }
        }
        return $this;
    }
}
