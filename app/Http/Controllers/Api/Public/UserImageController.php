<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Services\ImageCleanupService;
use App\Support\UserImages;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserImageController extends Controller
{
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);
        $folder = 'user_'.auth()->user()->id;
        $path = UserImages::store($validated['file'], "images/users/$folder");
        $url = Storage::url($path);
        $image = new Image;
        $image->url = $url;
        $image->mime = Storage::disk('public')->mimeType($path);
        $image->size = Storage::disk('public')->size($path);
        $image->user()->associate(auth()->user());
        $image->save();

        return response()->json(['id' => $image->id, 'url' => $url]);
    }

    public function destroy($id, ImageCleanupService $cleanup)
    {
        try {
            $image = Image::findOrFail($id);
            $this->authorize('delete', $image);
            $image->delete();
            $cleanup->cleanup([$image->id]);

            return response()->json(['message' => 'Файл успешно удален']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Файл не найден'], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }
}
