<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class AvatarController extends Controller
{
    public function show()
    {
        $id = auth()->user()->id;
        $user = User::findOrFail($id);
        $avatar = $user->avatar;
        if ($avatar) {
            return response()->json([
                'uri' =>  Storage::url($avatar->uri),
                'name' => basename($avatar->uri),
                // 'mime' => $avatar->mime,
                'size' => $avatar->size
            ]);
        }
    }

    public function upload(Request $request)
    {
        $data = $request->validate(['avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',]);

        $id = auth()->user()->id;
        $user = User::findOrFail($id);
        $avatar = $user->avatar;
        if ($avatar) {
            Storage::disk('public')->delete($avatar->uri);
        }

        $uri = $request->file('avatar')->store("/images/users/user_$id", 'public');
        $data = [
            'uri' => $uri,
            'mime' => Storage::disk('public')->mimeType($uri),
            'size' => Storage::disk('public')->size($uri),
        ];

        $user->avatar()->updateOrCreate(['user_id' => $user->id], $data);
        return response()->json($data);
    }

    public function destroy()
    {
        $id = auth()->user()->id;
        $user = User::findOrFail($id);
        $avatar = $user->avatar;
        if ($avatar) {
            Storage::disk('public')->delete($avatar->uri);
            $user->avatar()->delete();
            return response()->json(['message' => 'Файл успешно удален']);
        }
    }
}
