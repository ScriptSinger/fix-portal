<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Отклоняет адреса одноразовых почтовых сервисов (список в config/disposable_emails.php).
 */
class NotDisposableEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $domain = strtolower((string) substr((string) strrchr((string) $value, '@'), 1));

        if ($domain === '') {
            return;
        }

        foreach ((array) config('disposable_emails.domains', []) as $blocked) {
            if ($domain === $blocked || str_ends_with($domain, '.' . $blocked)) {
                $fail('Адреса временной почты не принимаются. Укажите постоянный e-mail.');

                return;
            }
        }
    }
}
