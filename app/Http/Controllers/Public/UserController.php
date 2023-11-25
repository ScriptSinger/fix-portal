<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Laravel\Socialite\Facades\Socialite;

class UserController extends Controller
{

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
}
