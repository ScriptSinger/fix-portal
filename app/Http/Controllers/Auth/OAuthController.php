<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Services\SocialLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class OAuthController extends Controller
{
    public function redirect(string $provider): RedirectResponse
    {
        abort_unless(array_key_exists($provider, SocialLogin::enabled()), 404);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider, SocialLogin $login): RedirectResponse
    {
        abort_unless(array_key_exists($provider, SocialLogin::enabled()), 404);

        $label = SocialLogin::PROVIDERS[$provider];

        try {
            $user = $login->resolve($provider, Socialite::driver($provider)->user());
        } catch (Throwable $e) {
            // Отказ пользователя, просроченный state или сбой сервиса: без 500, причина в лог
            Log::warning('Вход через соцсеть не удался', ['provider' => $provider, 'error' => $e->getMessage()]);

            return redirect()->route('login')->withErrors(['email' => "Не удалось войти через {$label}. Попробуйте ещё раз или войдите по почте."]);
        }

        if (! $user) {
            return redirect()->route('login')->withErrors([
                'email' => "{$label} не передал адрес почты или аккаунт с ним недоступен. Войдите или зарегистрируйтесь по почте.",
            ]);
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}
