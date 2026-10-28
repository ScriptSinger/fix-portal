<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // отписка в один клик: запрос приходит от почтовой программы без нашей сессии, защита это секрет в адресе
        'email/unsubscribe/*',
    ];
}
