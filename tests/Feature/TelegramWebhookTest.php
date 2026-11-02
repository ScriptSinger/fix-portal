<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Appliance;
use App\Models\Question;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

final class TelegramWebhookTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.telegram.webhook_secret' => 'test-webhook-secret']);
        config(['services.telegram.bot_token' => 'test-token']);
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

    public function test_authorized_moderator_can_use_help_command(): void
    {
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 10]])]);
        config(['services.telegram.moderator_ids' => [777]]);

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-webhook-secret')
            ->postJson('/api/telegram/webhook', [
                'update_id' => 1004,
                'message' => [
                    'message_id' => 8,
                    'text' => '/help@fix_portal_bot',
                    'from' => ['id' => 777, 'username' => 'moderator'],
                    'chat' => ['id' => -100123, 'type' => 'supergroup'],
                ],
            ])
            ->assertOk()
            ->assertJson(['ok' => true]);

        Http::assertSent(fn ($request): bool => $request->url() === 'https://api.telegram.org/bottest-token/sendMessage'
            && $request['chat_id'] === -100123
            && str_contains($request['text'], '/status'));
        $this->assertDatabaseHas('telegram_webhook_updates', [
            'update_id' => 1004,
        ]);
        $this->assertNotNull(
            \Illuminate\Support\Facades\DB::table('telegram_webhook_updates')->where('update_id', 1004)->value('processed_at')
        );
    }

    public function test_unauthorized_user_cannot_use_moderator_commands(): void
    {
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 11]])]);
        config(['services.telegram.moderator_ids' => [777]]);

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-webhook-secret')
            ->postJson('/api/telegram/webhook', [
                'update_id' => 1005,
                'message' => [
                    'message_id' => 9,
                    'text' => '/status',
                    'from' => ['id' => 778],
                    'chat' => ['id' => 123, 'type' => 'private'],
                ],
            ])
            ->assertOk();

        Http::assertSent(fn ($request): bool => $request['chat_id'] === 123
            && $request['text'] === 'Нет доступа к командам модератора.');
    }

    public function test_moderator_can_block_a_site_user_and_action_is_audited(): void
    {
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 12]])]);
        config(['services.telegram.moderator_ids' => [777]]);
        $user = User::factory()->create();

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-webhook-secret')
            ->postJson('/api/telegram/webhook', [
                'update_id' => 1006,
                'message' => [
                    'message_id' => 10,
                    'text' => '/block '.$user->id,
                    'from' => ['id' => 777],
                    'chat' => ['id' => -100123, 'type' => 'supergroup'],
                ],
            ])
            ->assertOk();

        $this->assertNotNull($user->fresh()->blocked_at);
        $this->assertDatabaseHas('telegram_moderation_actions', [
            'moderator_telegram_id' => 777,
            'user_id' => $user->id,
            'action' => 'block',
        ]);
        Http::assertSent(fn ($request): bool => str_contains($request['text'], 'заблокирован'));
    }

    public function test_mute_blocks_content_creation_until_expiry(): void
    {
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 13]])]);
        config(['services.telegram.moderator_ids' => [777], 'services.telegram.mute_minutes' => 60]);
        $user = User::factory()->create();

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-webhook-secret')
            ->postJson('/api/telegram/webhook', [
                'update_id' => 1007,
                'message' => [
                    'message_id' => 11,
                    'text' => '/mute '.$user->id,
                    'from' => ['id' => 777],
                    'chat' => ['id' => -100123, 'type' => 'supergroup'],
                ],
            ])
            ->assertOk();

        $this->assertTrue($user->fresh()->muted_until->isFuture());
        $this->assertDatabaseHas('telegram_moderation_actions', [
            'moderator_telegram_id' => 777,
            'user_id' => $user->id,
            'action' => 'mute',
        ]);
    }

    public function test_message_from_source_chat_is_queued_for_moderation(): void
    {
        config(['services.telegram.source_chat_id' => '-100123']);

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-webhook-secret')
            ->postJson('/api/telegram/webhook', [
                'update_id' => 1008,
                'message' => [
                    'message_id' => 12,
                    'text' => "Холодильник не морозит\nМодель RB33",
                    'from' => ['id' => 778],
                    'chat' => ['id' => -100123, 'type' => 'supergroup'],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('telegram_question_messages', [
            'telegram_chat_id' => -100123,
            'telegram_message_id' => 12,
            'telegram_update_id' => 1008,
            'status' => 'pending_moderation',
            'text' => "Холодильник не морозит\nМодель RB33",
        ]);
    }

    public function test_reply_in_source_chat_is_not_queued_as_a_new_question(): void
    {
        config(['services.telegram.source_chat_id' => '-100123']);

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-webhook-secret')
            ->postJson('/api/telegram/webhook', [
                'update_id' => 10081,
                'message' => [
                    'message_id' => 13,
                    'text' => 'Это обычный ответ на вопрос.',
                    'from' => ['id' => 778],
                    'chat' => ['id' => -100123, 'type' => 'supergroup'],
                    'reply_to_message' => ['message_id' => 12],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseMissing('telegram_question_messages', ['telegram_message_id' => 13]);
    }

    public function test_moderator_can_preview_and_approve_a_queued_question(): void
    {
        Http::fake(['https://api.telegram.org/*' => Http::sequence()
            ->push(['ok' => true, 'result' => ['message_id' => 900]])
            ->push(['ok' => true, 'result' => ['message_id' => 901]])
            ->push(['ok' => true, 'result' => ['message_id' => 902]])]);
        config([
            'services.telegram.source_chat_id' => '-100123',
            'services.telegram.moderation_chat_id' => '-100123',
            'services.telegram.moderator_ids' => [777],
        ]);
        $author = User::factory()->create();
        $appliance = Appliance::create(['title' => 'Холодильники']);
        config(['services.telegram.author_user_id' => $author->id]);

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-webhook-secret')
            ->postJson('/api/telegram/webhook', [
                'update_id' => 1009,
                'message' => [
                    'message_id' => 50,
                    'text' => "Холодильник Samsung не морозит\nНужна помощь мастера.",
                    'from' => ['id' => 778],
                    'chat' => ['id' => -100123, 'type' => 'supergroup', 'title' => 'Ufamasters'],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('telegram_question_messages', [
            'telegram_message_id' => 50,
            'status' => 'pending_moderation',
            'moderation_message_id' => 900,
        ]);
        Http::assertSent(fn ($request) => $request->data()['text'] === 'Сообщение получено и передано на модерацию.');

        $result = app(\App\Services\TelegramQuestionModerationService::class)
            ->approve(50, $appliance->id, null);

        $this->assertDatabaseHas('telegram_question_messages', [
            'telegram_message_id' => 50,
            'status' => 'published',
        ]);
        $this->assertStringContainsString('Вопрос опубликован', $result);
        $this->assertDatabaseCount('questions', 1);
        $this->assertDatabaseHas('questions', ['user_id' => $author->id, 'appliance_id' => $appliance->id]);
        $this->assertDatabaseHas('telegram_moderation_actions', ['action' => 'approve']);
        Http::assertSent(fn ($request) => data_get($request->data(), 'chat_id') === -100123
            && data_get($request->data(), 'reply_to_message_id') === 50
            && str_contains((string) data_get($request->data(), 'text'), 'Вопрос опубликован на Ufamasters'));
    }
}
