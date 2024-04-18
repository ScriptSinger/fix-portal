<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostImageController extends Controller
{
    public function index()
    {
        $images = Image::with('administrator', 'user')->get();
        return response()->json($images);
    }

    public function show($id)
    {
        $image = Image::findOrFail($id);
        return response()->json($image);
    }

    public function upload(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        $folder = date('Y-m-d');
        $path = $validated['file']->store("images/posts/$folder", 'public');
        $url = Storage::url($path);
        $image = new Image();
        $image->url = $url;
        $image->mime  = Storage::disk('public')->mimeType($path);
        $image->size  = Storage::disk('public')->size($path);
        $image->administrator()->associate(auth()->guard('admin')->user());
        $image->save();
        return response()->json(['id' => $image->id, 'url' => $url]);
    }

    public function destroy($id)
    {
        try {
            $image = Image::findOrFail($id);
            Storage::disk('public')->delete($image->url);
            $image->delete();
            return response()->json(['message' => 'Файл успешно удален']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Файл не найден'], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }
}
