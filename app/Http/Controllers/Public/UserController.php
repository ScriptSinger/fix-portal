<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class UserController extends Controller
{

    public function edit()
    {
        $user = auth()->user()->load('avatar');

        return view('public.users.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:30',
            'phone' => ['nullable', 'string', 'max:255', 'regex:/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/'],
            'location' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:512',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'remove_avatar' => 'nullable|boolean',
            // пароль меняем, только если заполнено новое поле
            'current_password' => ['required_with:password', 'nullable', 'current_password:web'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ], [
            'current_password.required_with' => 'Введите текущий пароль.',
            'current_password.current_password' => 'Текущий пароль указан неверно.',
            'password.confirmed' => 'Пароли не совпадают.',
            'phone.regex' => 'Телефон в формате +7 (900) 000-00-00.',
        ]);

        $user = $request->user();
        // пустое имя не затираем: оно нужно для подписи сообщений
        $user->fill(Arr::only($data, ['phone', 'location', 'bio']));
        if (! empty($data['name'])) {
            $user->name = $data['name'];
        }
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        if ($request->hasFile('avatar')) {
            $this->saveAvatar($user, $request->file('avatar'));
        } elseif ($request->boolean('remove_avatar')) {
            $this->removeAvatar($user);
        }

        return redirect()->route('profile.edit')->with('success', 'Изменения сохранены');
    }

    private function saveAvatar(User $user, UploadedFile $file): void
    {
        $this->removeAvatar($user);
        $uri = $file->store("images/users/user_{$user->id}", 'public');
        $user->avatar()->updateOrCreate(['user_id' => $user->id], [
            'uri' => $uri,
            'mime' => Storage::disk('public')->mimeType($uri),
            'size' => Storage::disk('public')->size($uri),
        ]);
    }

    private function removeAvatar(User $user): void
    {
        if ($avatar = $user->avatar) {
            Storage::disk('public')->delete($avatar->uri);
            $avatar->delete();
        }
    }

    /*
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $user = Socialite::driver('google')->user();

        // Проверяем, есть ли пользователь с таким email в базе данных
        $existingUser = User::where('email', $user->email)->first();

        if ($existingUser) {
            // Если пользователь существует, авторизуем его
            Auth::login($existingUser);
        } else {
            // Иначе, создаем нового пользователя
            $newUser = new User();
            $newUser->name = $user->name;
            $newUser->email = $user->email;
            // Дополнительные поля, если нужны
            // $newUser->save();
            Auth::login($newUser);
        }

        // Перенаправляем пользователя после авторизации
        return redirect()->route('admin.index')->with('success', 'Регистрация пройдена');
    }
    */
}
