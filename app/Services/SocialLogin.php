<?php

namespace App\Services;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialUser;

/**
 * Вход и регистрация через Яндекс ID и VK ID.
 *
 * Адрес почты из соцсети считаем подтверждённым: оба сервиса отдают только проверенные адреса.
 * Аккаунт сайта находим по связке «соцсеть + id», а не по почте, и к существующему пользователю
 * привязываем только по совпавшей почте.
 */
class SocialLogin
{
    public const PROVIDERS = [
        'yandex' => 'Яндекс',
        'vkid' => 'VK',
    ];

    /**
     * Провайдеры, для которых заданы ключи приложения: остальные кнопки не показываем.
     *
     * @return array<string, string>
     */
    public static function enabled(): array
    {
        return array_filter(
            self::PROVIDERS,
            fn (string $label, string $provider) => filled(config("services.{$provider}.client_id"))
                && filled(config("services.{$provider}.client_secret")),
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * Находит или создаёт пользователя сайта. null, если по данным соцсети это невозможно
     * (нет почты или аккаунт с такой почтой заблокирован).
     */
    public function resolve(string $provider, SocialUser $social): ?User
    {
        $providerId = (string) $social->getId();

        if ($providerId === '') {
            return $this->refuse($provider, 'no_provider_id', $social);
        }

        $link = SocialAccount::where('provider', $provider)->where('provider_user_id', $providerId)->first();

        if ($link) {
            return $link->user;
        }

        $email = Str::lower(trim((string) $social->getEmail()));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->refuse($provider, 'no_email', $social);
        }

        return DB::transaction(function () use ($provider, $providerId, $email, $social): ?User {
            // withTrashed: удалённый аккаунт занимает почту, заводить второй с тем же адресом нельзя
            $user = User::withTrashed()->where('email', $email)->first();

            if ($user?->trashed()) {
                return $this->refuse($provider, 'deleted_account', $social);
            }

            if ($user && $user->email_verified_at === null) {
                // Аккаунт с этой почтой создали по паролю и не подтвердили. Раз владелец почты вошёл через соцсеть,
                // он подтверждается, а пароль сбрасываем: иначе тот, кто регистрировал аккаунт на чужую почту, сохранил бы доступ
                $user->forceFill(['password' => Hash::make(Str::random(40)), 'remember_token' => null])->save();
                $user->markEmailAsVerified();
            }

            if (! $user) {
                $user = User::create([
                    'name' => $this->name($social, $email),
                    'email' => $email,
                    'password' => Hash::make(Str::random(40)),
                ]);
                $user->markEmailAsVerified();
            }

            SocialAccount::create(['user_id' => $user->id, 'provider' => $provider, 'provider_user_id' => $providerId]);

            return $user;
        });
    }

    /**
     * Отказ без исключения: пишем причину и имена полей, которые вернула соцсеть (без значений, чтобы не хранить личные данные).
     */
    private function refuse(string $provider, string $reason, SocialUser $social): ?User
    {
        Log::warning('Вход через соцсеть отклонён', [
            'provider' => $provider,
            'reason' => $reason,
            'fields' => array_keys((array) ($social->user ?? [])),
        ]);

        return null;
    }

    private function name(SocialUser $social, string $email): string
    {
        // VK отдаёт имена уже с HTML-сущностями («Al&#39;mir»): декодируем, иначе на сайте они видны как есть
        $name = trim(html_entity_decode((string) ($social->getName() ?: $social->getNickname() ?: Str::before($email, '@')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        // В форме регистрации имя ограничено 30 символами, держим то же правило
        return Str::limit($name, 30, '');
    }
}
