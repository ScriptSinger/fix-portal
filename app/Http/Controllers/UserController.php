<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

    // public function showRegistrationForm()
    // {
    //     return view('user.register');
    // }

    // public function register(Request $request): RedirectResponse
    // {
    //     $request->validate([
    //         'name' => ['required', 'string', 'max:255'],
    //         'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
    //         'password' => ['required', 'string', 'min:8', 'confirmed'],
    //     ]);

    //     $user = User::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'password' => Hash::make($request->password)
    //         // 'password' => bcrypt($request->password)
    //     ]);
    //     Auth::login($user);
    //     return redirect()->route('admin.index')->with('success', 'Регистрация пройдена');
    // }

    // public function login()
    // {
    //     return view('user.login');
    // }

    // public function logout(): RedirectResponse
    // {
    //     Auth::logout();
    //     return redirect()->route('login');
    // }

    // public function authenticate(Request $request): RedirectResponse
    // {
    //     $credentials = $request->validate([
    //         'email' => 'required | email',
    //         'password' => 'required'
    //     ]);
    //     if (Auth::attempt($credentials)) {
    //         $request->session()->regenerate();
    //         return redirect()->route('admin.index')->with('success', 'Вы вошли успешно');
    //     }

    //     return back()->withErrors([
    //         'email' => 'The provided credentials do not match our records.',
    //     ])->onlyInput('email');
    // }
}
