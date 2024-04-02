<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ThumbnailService
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new ThumbnailService();
        }
        return self::$instance;
    }

    public function store(UploadedFile $file)
    {
        $tempPath = $file->path();
        $image = Image::read($tempPath);

        $dir = date('Y-m-d');
        $basePath = 'images/posts/thumbnails/' . $dir . '/';
        $name = $file->hashName();

        $blogDir = $basePath . 'blog/';
        Storage::disk('public')->makeDirectory($blogDir);

        $smallDir = $basePath . 'small/';
        Storage::disk('public')->makeDirectory($smallDir);

        $blogPath = Storage::disk('public')->path($blogDir . $name);
        $image->cover(800, 460);
        $image->toJpeg()->save($blogPath);

        $smallPath = Storage::disk('public')->path($smallDir . $name);
        $image->cover(90, 90);
        $image->toJpeg()->save($smallPath);

        return [
            'original' => Storage::url($file->store($basePath . 'original', 'public')),
            'blog' =>   Storage::url($blogDir . $name),
            'small' => Storage::url($smallDir . $name),
        ];
    }

    public function destroy(array $paths)
    {
        foreach ($paths as $path) {
            Storage::disk('public')->delete($path);
        }
        return $this;
    }
}
