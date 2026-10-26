<?php

namespace App\Socialite;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use RuntimeException;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

/**
 * Вход через VK ID (OAuth 2.1 с PKCE, https://id.vk.ru).
 *
 * Готовый пакет socialiteproviders/vkid требует PHP 8.4, а сайт работает на 8.2, поэтому провайдер написан здесь.
 * Отличие VK ID от обычного OAuth: в запрос обмена кода на токен нужно передать device_id из адреса возврата.
 */
class VkIdProvider extends AbstractProvider
{
    public const IDENTIFIER = 'VKID';

    protected $stateless = false;

    protected $usesPKCE = true;

    protected $scopes = ['email'];

    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://id.vk.ru/authorize', $state);
    }

    protected function getTokenUrl()
    {
        return 'https://id.vk.ru/oauth2/auth';
    }

    protected function getTokenFields($code)
    {
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code',
            'device_id' => $this->request->input('device_id'),
            'state' => $this->request->input('state'),
        ]);
    }

    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->post('https://id.vk.ru/oauth2/user_info', [
            RequestOptions::HEADERS => ['Accept' => 'application/json'],
            RequestOptions::FORM_PARAMS => [
                'access_token' => is_array($token) ? $token['access_token'] : $token,
                'client_id' => $this->clientId,
            ],
        ]);

        $contents = (string) $response->getBody();
        $data = json_decode($contents, true);

        if (! is_array($data) || ! isset($data['user'])) {
            throw new RuntimeException('Неверный ответ VK ID: ' . $contents);
        }

        return $data['user'];
    }

    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'id' => (string) Arr::get($user, 'user_id'),
            'name' => trim(Arr::get($user, 'first_name') . ' ' . Arr::get($user, 'last_name')),
            'email' => Arr::get($user, 'email'),
            'avatar' => Arr::get($user, 'avatar'),
        ]);
    }
}
