<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\UpdateProfileRequest;
use App\Services\AvatarService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function edit()
    {
        $user = auth()->user()->load('avatar');

        return view('public.users.edit', compact('user'));
    }

    public function update(UpdateProfileRequest $request, AvatarService $avatars)
    {
        $data = $request->validated();
        $user = $request->user();

        $user->fill(Arr::only($data, ['phone', 'location', 'bio']));
        // пустое имя не затираем: оно нужно для подписи сообщений
        if (! empty($data['name'])) {
            $user->name = $data['name'];
        }
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        if ($request->hasFile('avatar')) {
            $avatars->replace($user, $request->file('avatar'));
        } elseif ($request->boolean('remove_avatar')) {
            $avatars->remove($user);
        }

        return redirect()->route('profile.edit')->with('success', 'Изменения сохранены');
    }
}
