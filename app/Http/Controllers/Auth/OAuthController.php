<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{

    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        $socialUser = Socialite::driver($provider)->user();

        $user = User::firstOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'name' => $socialUser->getName(),
                'password' => bcrypt(str()->random(16)),
                // Можно сохранить аватарку, id соцсети и т.д.
                // $provider . '_id' => $socialUser->getId(),
            ]
        );

        // Если соцсеть подтверждает email — сразу отмечаем как verified
        if ($user->email_verified_at === null) {
            $user->markEmailAsVerified();
        }
        Auth::login($user);

        return redirect()->intended('/');
    }
}
