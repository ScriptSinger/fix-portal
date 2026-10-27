<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/** Аватар пользователя: хранится на диске public, запись о нём в таблице avatars. */
class AvatarService
{
    public function replace(User $user, UploadedFile $file): void
    {
        $this->remove($user);
        $uri = $file->store("images/users/user_{$user->id}", 'public');

        $user->avatar()->updateOrCreate(['user_id' => $user->id], [
            'uri' => $uri,
            'mime' => Storage::disk('public')->mimeType($uri),
            'size' => Storage::disk('public')->size($uri),
        ]);
    }

    public function remove(User $user): void
    {
        if ($avatar = $user->avatar) {
            Storage::disk('public')->delete($avatar->uri);
            $avatar->delete();
        }
    }
}
