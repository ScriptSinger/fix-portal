<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class TelegramBotApi
{
    public function call(string $method, array $payload = []): array
    {
        $token = (string) config('services.telegram.bot_token');
        if ($token === '') {
            throw new \RuntimeException('TELEGRAM_BOT_TOKEN is not configured.');
        }

        $url = rtrim((string) config('services.telegram.api_url'), '/').'/bot'.$token.'/'.$method;

        try {
            $response = Http::timeout(15)
                ->withOptions(array_filter(['proxy' => config('services.telegram.proxy')]))
                ->post($url, $payload)
                ->throw();
        } catch (RequestException $e) {
            $description = (string) $e->response->json('description');

            throw new \RuntimeException('Telegram API: '.($description !== '' ? $description : 'HTTP '.$e->response->status()));
        } catch (ConnectionException $e) {
            throw new \RuntimeException('Telegram API connection failed.');
        }

        if (! $response->json('ok')) {
            throw new \RuntimeException('Telegram API returned an unsuccessful response.');
        }

        return (array) $response->json('result');
    }

    public function downloadFile(string $filePath): string
    {
        $token = (string) config('services.telegram.bot_token');
        if ($token === '') {
            throw new \RuntimeException('TELEGRAM_BOT_TOKEN is not configured.');
        }

        $url = rtrim((string) config('services.telegram.api_url'), '/').'/file/bot'.$token.'/'.ltrim($filePath, '/');

        try {
            return Http::timeout(30)
                ->withOptions(array_filter(['proxy' => config('services.telegram.proxy')]))
                ->get($url)
                ->throw()
                ->body();
        } catch (RequestException|ConnectionException $e) {
            throw new \RuntimeException('Telegram file download failed.');
        }
    }
}
