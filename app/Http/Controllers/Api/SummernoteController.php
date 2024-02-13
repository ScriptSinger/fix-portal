<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadSummernoteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SummernoteController extends Controller
{
    public function upload(UploadSummernoteRequest $request)
    {
        $folder = date('Y-m-d');
        $path = $request->file('file')->store("summernote/$folder", 'public');
        $url = Storage::url($path);
        return response()->json($url);
    }

    public function destroy(Request $request)
    {
        $src = $request->input('src');
        $path = parse_url($src, PHP_URL_PATH);
        $path = str_replace('/storage', '', $path);
        Storage::disk('public')->delete($path);
    }
}
