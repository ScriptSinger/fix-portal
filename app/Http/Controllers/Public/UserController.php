<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\UpdateProfileRequest;
use App\Services\AvatarService;
use App\Services\LatestDiscussion;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function edit()
    {
        $user = auth()->user()->load('avatar');

        return view('public.users.edit', compact('user'));
    }

    public function update(UpdateProfileRequest $request, AvatarService $avatars, LatestDiscussion $latestDiscussion)
    {
        $data = $request->validated();
        $user = $request->user();

        $user->fill(Arr::only($data, ['username', 'phone', 'location', 'bio']));
        // пустое имя не затираем: оно нужно для подписи сообщений
        if (! empty($data['name'])) {
            $user->name = $data['name'];
        }
        // переключатель писем приходит всегда вместе с формой; запросы без него (старые формы) настройку не трогают
        if ($request->has('email_notifications')) {
            $user->email_notifications = $request->boolean('email_notifications');
        }
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        $avatarChanged = false;
        if ($request->hasFile('avatar')) {
            $avatars->replace($user, $request->file('avatar'));
            $avatarChanged = true;
        } elseif ($request->boolean('remove_avatar')) {
            $avatars->remove($user);
            $avatarChanged = true;
        }
        if ($avatarChanged) {
            $latestDiscussion->forget();
        }

        return redirect()->route('profile.edit')->with('success', 'Изменения сохранены');
    }
}
