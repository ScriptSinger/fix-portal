<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class UserController extends Controller
{
    public function index()
    {
        return view('public.users.index');
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('public.users.show', compact('user'));
    }

    public function edit()
    {
        $id = auth()->user()->id;
        $user = User::findOrFail($id);
        $roles = Role::pluck('name', 'id');
        return view('public.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
            // 'email' => 'required|string|email|max:255|unique:users,email,' . auth()->id(),
            'phone' => ['required', 'string', 'max:255', 'regex:/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/'],
            'location' => 'required|string|max:255',
            'bio' => 'required|string|max:512',
            'role_id' => 'nullable'
        ]);

        $user = $request->user();
        $user->update($data);
        return redirect()->route('users.index')->with('success', 'Изменения сохранены');
    }
















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
