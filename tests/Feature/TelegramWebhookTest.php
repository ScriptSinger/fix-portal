<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

final class TelegramWebhookTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.telegram.webhook_secret' => 'test-webhook-secret']);
    }

    public function test_webhook_rejects_requests_without_the_secret(): void
    {
        $this->postJson('/api/telegram/webhook', ['update_id' => 1001])
            ->assertUnauthorized();
    }

    public function test_webhook_rejects_invalid_updates(): void
    {
        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-webhook-secret')
            ->postJson('/api/telegram/webhook', ['message' => ['text' => 'without update id']])
            ->assertStatus(422);
    }

    public function test_webhook_stores_an_update_and_accepts_a_duplicate(): void
    {
        Log::spy();
        $payload = [
            'update_id' => 1002,
            'message' => [
                'message_id' => 7,
                'text' => 'Проверка webhook',
            ],
        ];

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-webhook-secret')
            ->postJson('/api/telegram/webhook', $payload)
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('telegram_webhook_updates', [
            'update_id' => 1002,
        ]);

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-webhook-secret')
            ->postJson('/api/telegram/webhook', $payload)
            ->assertOk()
            ->assertJson(['ok' => true, 'duplicate' => true]);

        $this->assertDatabaseCount('telegram_webhook_updates', 1);
    }

    public function test_webhook_rejects_an_oversized_payload(): void
    {
        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-webhook-secret')
            ->postJson('/api/telegram/webhook', [
                'update_id' => 1003,
                'message' => ['text' => str_repeat('x', 262145)],
            ])
            ->assertStatus(413);
    }
}
