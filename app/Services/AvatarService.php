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
        $uri = $file->store("images/users/user_{$user->id}", 'public');
        $attributes = [
            'uri' => $uri,
            'mime' => Storage::disk('public')->mimeType($uri),
            'size' => Storage::disk('public')->size($uri),
        ];
        $user->getConnection()->transaction(function () use ($user, $attributes): void {
            $this->remove($user);
            $user->avatar()->create($attributes);
        });
        $user->unsetRelation('avatar');
    }

    public function remove(User $user): void
    {
        if ($avatar = $user->avatar) {
            $avatar->delete();
        }
    }
}
