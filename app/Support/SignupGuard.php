<?php

namespace App\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Лёгкая защита формы регистрации без внешних сервисов:
 * скрытое поле-ловушка и проверка времени заполнения формы.
 */
class SignupGuard
{
    public const HONEYPOT_FIELD = 'website';

    public const TOKEN_FIELD = 'form_token';

    private const MIN_SECONDS = 3;

    private const MAX_SECONDS = 7200;

    /**
     * Токен для скрытого поля формы: зашифрованное время её показа.
     */
    public function token(): string
    {
        return Crypt::encryptString((string) now()->timestamp);
    }

    /**
     * Причина, по которой запрос похож на бота, или null, если всё в порядке.
     */
    public function reason(Request $request): ?string
    {
        if (filled($request->input(self::HONEYPOT_FIELD))) {
            return 'honeypot';
        }

        $token = $request->input(self::TOKEN_FIELD);

        if (! is_string($token) || $token === '') {
            return 'no_token';
        }

        try {
            $shownAt = (int) Crypt::decryptString($token);
        } catch (DecryptException) {
            return 'bad_token';
        }

        $age = now()->timestamp - $shownAt;

        return match (true) {
            $age < self::MIN_SECONDS => 'too_fast',
            $age > self::MAX_SECONDS => 'expired',
            default => null,
        };
    }

    /**
     * @throws ValidationException
     */
    public function assertHuman(Request $request): void
    {
        $reason = $this->reason($request);

        if ($reason === null) {
            return;
        }

        // Отдельный канал: пишется независимо от LOG_LEVEL, чтобы потом оценить, сколько ботов отсеялось
        Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/signup.log'),
            'days' => 30,
        ])->warning('signup_blocked', [
            'reason' => $reason,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        throw ValidationException::withMessages([
            'email' => 'Не удалось отправить форму. Обновите страницу и попробуйте ещё раз.',
        ]);
    }
}
