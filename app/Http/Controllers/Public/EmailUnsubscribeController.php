<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\User;

/**
 * Отключение писем об активности по ссылке из письма, без входа в аккаунт. Адрес открывает страницу с кнопкой
 * (почтовые сканеры открывают ссылки сами, поэтому GET ничего не отключает), а POST по тому же адресу отключает:
 * его же вызывает почтовая программа при отписке в один клик (заголовок List-Unsubscribe-Post).
 */
class EmailUnsubscribeController extends Controller
{
    public function show(string $token)
    {
        $user = User::where('unsubscribe_token', $token)->firstOrFail();

        return view('public.email.unsubscribe', ['token' => $token, 'done' => ! $user->email_notifications]);
    }

    public function update(string $token)
    {
        abort_unless(User::where('unsubscribe_token', $token)->update(['email_notifications' => false]), 404);

        return view('public.email.unsubscribe', ['token' => $token, 'done' => true]);
    }
}
