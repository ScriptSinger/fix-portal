<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthSessionController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/admin';

    public function showLoginForm()
    {
        return view('admin.auth.login'); // Отображение формы входа администраторов
    }

    protected function guard()
    {
        return Auth::guard('admin'); // Используйте гвард 'admin' для аутентификации администраторов
    }
}
