<?php

namespace App\Http\Controllers\MoonShine;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class TinyMceImageUploadController
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            // TinyMCE sends the file in "file"
            'file' => ['required', 'file', 'image', 'max:10240'],
        ]);

        /** @var \Illuminate\Http\UploadedFile $file */
        $file = $validated['file'];

        $disk = (string) config('filesystems.media');
        $path = $file->storePublicly('galleries/content', ['disk' => $disk]);

        if (!is_string($path) || $path === '') {
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Upload failed');
        }

        return response()->json([
            // TinyMCE expects { location: "https://..." }
            'location' => Storage::disk($disk)->url($path),
        ]);
    }
}

