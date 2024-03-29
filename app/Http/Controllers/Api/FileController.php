<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function index()
    {
        $files = File::with('administrator', 'user')->get();
        return response()->json($files);
    }

    public function show($id)
    {
        $file = File::findOrFail($id);
        return response()->json($file);
    }



    public function upload(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $folder = date('Y-m-d');
        $path = $validated['file']->store("posts/$folder", 'public');
        $url = Storage::url($path);
        $file = new File();
        $file->url = $url;
        $file->mime  = Storage::disk('public')->mimeType($path);
        $file->size  = Storage::disk('public')->size($path);
        $file->administrator()->associate(auth()->guard('admin')->user());
        $file->save();
        return response()->json(['id' => $file->id, 'url' => $url]);
    }


    public function destroy($id)
    {
        try {
            $file = File::findOrFail($id);
            Storage::disk('public')->delete($file->url);
            $file->delete();
            return response()->json(['message' => 'Файл успешно удален']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Файл не найден'], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }
}
