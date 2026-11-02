<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class TelegramWebhookCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.telegram.bot_token' => 'test-token',
            'services.telegram.proxy' => 'http://proxy.invalid:8080',
            'services.telegram.webhook_url' => 'https://example.test/api/telegram/webhook',
            'services.telegram.webhook_secret' => 'test-secret',
        ]);
    }

    public function test_set_command_registers_webhook(): void
    {
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => true])]);

        $this->artisan('telegram:webhook', ['action' => 'set'])
            ->expectsOutputToContain('Telegram webhook registered')
            ->assertSuccessful();

        Http::assertSent(fn ($request) => $request->url() === 'https://api.telegram.org/bottest-token/setWebhook'
            && $request['url'] === 'https://example.test/api/telegram/webhook'
            && $request['secret_token'] === 'test-secret');
    }

    public function test_info_command_outputs_webhook_info(): void
    {
        Http::fake(['https://api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => ['url' => 'https://example.test/api/telegram/webhook', 'pending_update_count' => 0],
        ])]);

        $this->artisan('telegram:webhook', ['action' => 'info'])
            ->expectsOutputToContain('pending_update_count')
            ->assertSuccessful();
    }

    public function test_delete_command_removes_webhook(): void
    {
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => true])]);

        $this->artisan('telegram:webhook', ['action' => 'delete'])
            ->expectsOutputToContain('Telegram webhook deleted')
            ->assertSuccessful();
    }
}
