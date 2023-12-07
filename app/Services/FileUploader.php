<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class FileUploader
{
    private $data;
    private $model;
    private $subDir;
    private $supDir;

    public static function getInstance()
    {
        return new self();
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
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

    public function setSubDir($subDir)
    {
        $this->subDir = $subDir;
        return $this;
    }

    public function setSupDir($supDir)
    {
        $this->supDir = $supDir;
        return $this;
    }

    private function makeDir($dir)
    {
        $finalDir = '';

        // Добавляем надкаталог, если установлен
        if ($this->supDir) {
            $finalDir .= $this->supDir . '/';
        }

        // Добавляем основной каталог
        $finalDir .= $dir;

        // Добавляем подкаталог, если установлен
        if ($this->subDir) {
            $finalDir .= '/' . $this->subDir;
        }

        Storage::disk('public')->makeDirectory($finalDir);
        // File::makeDirectory(storage_path("app/public/$finalDir"), 0755, true, true);
        return $finalDir;
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

    public function save()
    {
        foreach ($this->data as $key => $value) {
            if (file_exists($value)) {
                $dir = $this->makeDir($key);
                $this->data[$key] = $value->store($dir, 'public');
            }
        }
        return $this;
    }

    // если не создать директорию avatar получаем exception
    public function resizeSave($width = null, $height = null)
    {
        $width = $width ?? 200;
        $height = $height ?? 200;

        foreach ($this->data as $key => $value) {
            if (file_exists($value)) {
                $dir = $this->makeDir($key);

                $path = storage_path("app/public/$dir/" . $value->hashName());

                Image::make($value->path())
                    ->resize($width, $height)
                    ->save($path);

                $this->data[$key] = $key . "/" . $value->hashName();
            }
        }
        return $this;
    }

    public function multipleRemovePrev()
    {
        if ($this->model) {
            foreach ($this->data as $key => $value) {
                if (is_array($value)) {
                    $array = json_decode($this->model->$key);
                    foreach ($array as $k => $v) {
                        Storage::disk('public')->delete($v);
                    }
                }
            }
        }
        return $this;
    }

    public function multipleSave($subDir = null)
    {
        foreach ($this->data as $key => $value) {
            if (is_array($value)) {

                $array = [];
                foreach ($value as $uploadedFile) {
                    if ($uploadedFile->isValid()) {
                        $dir = $this->makeDir($key, $subDir);
                        $array[] = $uploadedFile->store($dir, 'public');
                    }
                }
                $this->data[$key] = json_encode($array);
            }
        }
        return $this;
    }
}
