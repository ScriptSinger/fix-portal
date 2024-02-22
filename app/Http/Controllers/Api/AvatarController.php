<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AvatarUploadRequest;
use App\Models\User;

use Illuminate\Support\Facades\Storage;

class AvatarController extends Controller
{
    public function upload(AvatarUploadRequest $request)
    {
        $id = auth()->user()->id;
        $user = User::findOrFail($id);
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('file')->store("avatar", 'public');
        $user->update(['avatar' => $path]);
        return response()->json($path);
    }

    public function show()
    {
        $id = auth()->user()->id;
        $user = User::findOrFail($id);
        if ($user->avatar) {
            return response()->json([
                'path' => asset('storage/' . $user->avatar),
                'size' => Storage::disk('public')->size($user->avatar),
                'name' => str_replace('avatar/', '', $user->avatar)
            ]);
        }
    }

    public function destroy()
    {
        $id = auth()->user()->id;
        $user = User::findOrFail($id);
        if ($user->avatar) {
            $path = Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
            return response()->json($path);
        }
    }
}
