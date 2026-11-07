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
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class TelegramWebhookTest extends TestCase
{
    use DatabaseTransactions;

    // Nutgram validates Telegram's required DTO fields. Supply transport metadata
    // omitted by the historical business-scenario fixtures, without changing IDs/text.
    public function postJson($uri, array $data = [], array $headers = [], $options = 0)
    {
        $completeMessage = function (array $message, ?array $chat = null) use (&$completeMessage): array {
            $message['date'] ??= now()->timestamp;
            if ($chat !== null) {
                $message['chat'] ??= $chat;
            }
            if (isset($message['from'])) {
                $message['from'] += ['is_bot' => false, 'first_name' => 'Test'];
            }
            foreach ($message['photo'] ?? [] as $i => $photo) {
                $message['photo'][$i] += ['file_unique_id' => $photo['file_id'], 'width' => 100, 'height' => 100];
            }
            if (isset($message['reply_to_message'])) {
                $message['reply_to_message'] = $completeMessage($message['reply_to_message'], $message['chat'] ?? null);
            }
            return $message;
        };
        if (isset($data['message'])) {
            $data['message'] = $completeMessage($data['message']);
        }
        if (isset($data['callback_query'])) {
            $data['callback_query']['chat_instance'] ??= 'test-chat-instance';
            $data['callback_query']['from'] += ['is_bot' => false, 'first_name' => 'Test'];
            if (isset($data['callback_query']['message'])) {
                $data['callback_query']['message'] = $completeMessage($data['callback_query']['message']);
            }
        }
        return parent::postJson($uri, $data, $headers, $options);
    }

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.telegram.webhook_secret' => 'test-webhook-secret']);
        config(['services.telegram.bot_token' => 'test-token']);
        config(['services.telegram.environment' => app()->environment()]);
    }

    public function test_webhook_rejects_requests_without_the_secret(): void
    {
        $this->postJson('/api/telegram/webhook', ['update_id' => 1001])
            ->assertUnauthorized();
    }

    public function test_webhook_rejects_a_mismatched_telegram_environment(): void
    {
        config(['services.telegram.environment' => 'production']);

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-webhook-secret')
            ->postJson('/api/telegram/webhook', ['update_id' => 10011])
            ->assertStatus(503)
            ->assertJson(['message' => 'Telegram environment is misconfigured']);
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

    public function test_webhook_monitor_reports_stale_updates(): void
    {
        DB::table('telegram_webhook_updates')->insert([
            'update_id' => 1099,
            'payload' => json_encode(['update_id' => 1099]),
            'received_at' => now()->subMinutes(10),
            'attempts' => 1,
            'failed_at' => now()->subMinutes(9),
            'processing_error' => 'Telegram API unavailable',
        ]);

        $this->artisan('telegram:webhook-monitor', ['--minutes' => 5, '--json' => true])
            ->assertExitCode(1)
            ->expectsOutputToContain('"stale_count": 1')
            ->expectsOutputToContain('"failed_count": 1');
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

    public function test_authorized_moderator_can_use_attach_command(): void
    {
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 14]])]);
        config(['services.telegram.moderator_ids' => [777]]);

        $queueId = DB::table('telegram_question_messages')->insertGetId([
            'telegram_chat_id' => -100123,
            'telegram_message_id' => 20,
            'telegram_update_id' => 2000,
            'telegram_user_id' => 778,
            'text' => 'Вопрос с фото',
            'payload' => json_encode(['message' => ['message_id' => 20, 'text' => 'Вопрос с фото']]),
            'status' => 'pending_moderation',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-webhook-secret')
            ->postJson('/api/telegram/webhook', [
                'update_id' => 2001,
                'message' => [
                    'message_id' => 21,
                    'text' => '/attach '.$queueId,
                    'from' => ['id' => 777],
                    'chat' => ['id' => -100123, 'type' => 'supergroup'],
                    'reply_to_message' => [
                        'message_id' => 19,
                        'text' => 'Дополнительная информация',
                    ],
                ],
            ])
            ->assertOk();

        Http::assertSent(fn ($request): bool => $request['chat_id'] === -100123
            && str_contains($request['text'], 'Сообщение добавлено к вопросу'));
        $this->assertDatabaseHas('telegram_question_messages', [
            'id' => $queueId,
        ]);
    }

    public function test_question_command_is_rate_limited(): void
    {
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 15]])]);
        config([
            'services.telegram.moderator_ids' => [990],
            'services.telegram.source_chat_id' => '-100990',
            'services.telegram.command_rate_limit_per_minute' => 1,
        ]);

        $payload = static fn (int $updateId): array => [
            'update_id' => $updateId,
            'message' => [
                'message_id' => $updateId,
                'text' => '/question',
                'from' => ['id' => 990],
                'chat' => ['id' => -100990, 'type' => 'supergroup'],
            ],
        ];

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-webhook-secret')
            ->postJson('/api/telegram/webhook', $payload(2010))
            ->assertOk();
        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-webhook-secret')
            ->postJson('/api/telegram/webhook', $payload(2011))
            ->assertOk();

        Http::assertSent(fn ($request): bool => str_contains($request['text'], 'Слишком много команд'));
    }

    public function test_marked_question_tells_the_author_about_thumbs_up_reaction(): void
    {
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 901]])]);
        config(['services.telegram.moderator_ids' => [777], 'services.telegram.source_chat_id' => '-100123']);

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-webhook-secret')
            ->postJson('/api/telegram/webhook', [
                'update_id' => 2095,
                'message' => [
                    'message_id' => 95,
                    'text' => '/question',
                    'from' => ['id' => 777],
                    'chat' => ['id' => -100123, 'type' => 'supergroup'],
                    'reply_to_message' => [
                        'message_id' => 94,
                        'text' => 'Почему холодильник не охлаждает?',
                        'from' => ['id' => 778],
                    ],
                ],
            ])->assertOk();

        Http::assertSent(fn ($request): bool => str_ends_with($request->url(), '/sendMessage')
            && data_get($request->data(), 'reply_to_message_id') === 94
            && str_contains((string) data_get($request->data(), 'text'), 'поставьте 👍'));
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

    public function test_question_mark_message_from_source_chat_requires_confirmation_before_moderation(): void
    {
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 900]])]);
        config([
            'services.telegram.source_chat_id' => '-100123',
            'services.telegram.moderation_chat_id' => '-100123',
        ]);

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-webhook-secret')
            ->postJson('/api/telegram/webhook', [
                'update_id' => 1008,
                'message' => [
                    'message_id' => 12,
                    'text' => "Холодильник не морозит?\nМодель RB33",
                    'from' => ['id' => 778],
                    'chat' => ['id' => -100123, 'type' => 'supergroup'],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('telegram_question_messages', [
            'telegram_chat_id' => -100123,
            'telegram_message_id' => 12,
            'telegram_update_id' => 1008,
            'status' => 'awaiting_confirmation',
            'text' => "Холодильник не морозит?\nМодель RB33",
        ]);

        Http::assertSent(fn ($request): bool => $request['chat_id'] === -100123
            && $request['text'] === 'Похоже, это вопрос. Зарегистрировать его?'
            && str_contains((string) $request['reply_markup'], 'question_confirm:'));
    }

    public function test_author_can_confirm_an_automatically_detected_question(): void
    {
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 901]])]);
        config([
            'services.telegram.source_chat_id' => '-100123',
            'services.telegram.moderation_chat_id' => '-100123',
        ]);

        $headers = ['X-Telegram-Bot-Api-Secret-Token' => 'test-webhook-secret'];
        $this->withHeaders($headers)->postJson('/api/telegram/webhook', [
            'update_id' => 1010,
            'message' => [
                'message_id' => 30,
                'text' => 'Компрессор не запускается?',
                'from' => ['id' => 778],
                'chat' => ['id' => -100123, 'type' => 'supergroup'],
            ],
        ])->assertOk();

        $record = DB::table('telegram_question_messages')->where('telegram_message_id', 30)->firstOrFail();

        $this->withHeaders($headers)->postJson('/api/telegram/webhook', [
            'update_id' => 1011,
            'callback_query' => [
                'id' => 'callback-1011',
                'data' => "question_confirm:{$record->id}",
                'from' => ['id' => 778],
                'message' => [
                    'message_id' => 901,
                    'chat' => ['id' => -100123, 'type' => 'supergroup'],
                ],
            ],
        ])->assertOk();

        $this->assertDatabaseHas('telegram_question_messages', [
            'id' => $record->id,
            'status' => 'pending_moderation',
        ]);
        Http::assertSent(fn ($request): bool => str_ends_with($request->url(), '/editMessageText')
            && str_contains((string) data_get($request->data(), 'text'), 'поставьте 👍'));
    }

    public function test_plain_message_from_source_chat_is_not_queued(): void
    {
        config(['services.telegram.source_chat_id' => '-100123']);

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-webhook-secret')
            ->postJson('/api/telegram/webhook', [
                'update_id' => 10081,
                'message' => [
                    'message_id' => 121,
                    'text' => 'Холодильник уже починили, спасибо.',
                    'from' => ['id' => 778],
                    'chat' => ['id' => -100123, 'type' => 'supergroup'],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseMissing('telegram_question_messages', [
            'telegram_message_id' => 121,
        ]);
    }

    public function test_consecutive_messages_from_the_same_author_are_merged_into_one_question(): void
    {
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 901]])]);
        config([
            'services.telegram.source_chat_id' => '-100123',
            'services.telegram.moderation_chat_id' => '-100123',
        ]);

        $headers = ['X-Telegram-Bot-Api-Secret-Token' => 'test-webhook-secret'];
        $baseMessage = [
            'from' => ['id' => 778],
            'chat' => ['id' => -100123, 'type' => 'supergroup'],
        ];

        $this->withHeaders($headers)->postJson('/api/telegram/webhook', [
            'update_id' => 2010,
            'message' => $baseMessage + [
                'message_id' => 201,
                'text' => 'Компрессор запускается, но не охлаждает?',
            ],
        ])->assertOk();

        $record = DB::table('telegram_question_messages')->where('telegram_message_id', 201)->firstOrFail();

        $this->withHeaders($headers)->postJson('/api/telegram/webhook', [
            'update_id' => 20105,
            'callback_query' => [
                'id' => 'callback-20105',
                'data' => "question_confirm:{$record->id}",
                'from' => ['id' => 778],
                'message' => [
                    'message_id' => 901,
                    'chat' => ['id' => -100123, 'type' => 'supergroup'],
                ],
            ],
        ])->assertOk();

        $this->withHeaders($headers)->postJson('/api/telegram/webhook', [
            'update_id' => 2011,
            'message' => $baseMessage + [
                'message_id' => 202,
                'photo' => [['file_id' => 'photo-202', 'width' => 100, 'height' => 100]],
                'reply_to_message' => ['message_id' => 201],
            ],
        ])->assertOk();

        $this->withHeaders($headers)->postJson('/api/telegram/webhook', [
            'update_id' => 2012,
            'message' => $baseMessage + [
                'message_id' => 203,
                'text' => 'Модель Indesit DF 5200 W.',
                'reply_to_message' => ['message_id' => 201],
            ],
        ])->assertOk();

        $this->assertDatabaseCount('telegram_question_messages', 1);
        $record = DB::table('telegram_question_messages')->first();
        $this->assertSame("Компрессор запускается, но не охлаждает?\n\nМодель Indesit DF 5200 W.", $record->text);
        $payload = json_decode((string) $record->payload, true, 32, JSON_THROW_ON_ERROR);
        $this->assertContains(202, $payload['attached_message_ids']);
        $this->assertContains(203, $payload['attached_message_ids']);
        Http::assertSentCount(3);
    }

    public function test_photo_without_text_is_not_created_as_an_orphan_question(): void
    {
        config(['services.telegram.source_chat_id' => '-100123']);

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-webhook-secret')
            ->postJson('/api/telegram/webhook', [
                'update_id' => 2013,
                'message' => [
                    'message_id' => 204,
                    'photo' => [['file_id' => 'photo-204', 'width' => 100, 'height' => 100]],
                    'from' => ['id' => 778],
                    'chat' => ['id' => -100123, 'type' => 'supergroup'],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseMissing('telegram_question_messages', ['telegram_message_id' => 204]);
        $this->assertDatabaseHas('telegram_webhook_updates', ['update_id' => 2013]);
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

    public function test_unknown_slash_command_is_not_queued_as_a_question(): void
    {
        config(['services.telegram.source_chat_id' => '-100123']);

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-webhook-secret')
            ->postJson('/api/telegram/webhook', [
                'update_id' => 10082,
                'message' => [
                    'message_id' => 14,
                    'text' => '/attach 8',
                    'from' => ['id' => 778],
                    'chat' => ['id' => -100123, 'type' => 'supergroup'],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseMissing('telegram_question_messages', ['telegram_message_id' => 14]);
    }

    public function test_photo_is_downloaded_and_attached_when_question_is_published(): void
    {
        Storage::fake('public');
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true);
        Http::fake(function ($request) use ($png) {
            if (str_contains($request->url(), '/getFile')) {
                return Http::response(['ok' => true, 'result' => ['file_path' => 'photos/question.png']]);
            }
            if (str_contains($request->url(), '/file/bot')) {
                return Http::response($png, 200, ['Content-Type' => 'image/png']);
            }

            return Http::response(['ok' => true, 'result' => ['message_id' => 901]]);
        });
        config(['services.telegram.source_chat_id' => '-100123', 'services.telegram.moderation_chat_id' => '-100999']);
        $author = User::factory()->create();
        $appliance = Appliance::create(['title' => 'Стиральные машины']);
        config(['services.telegram.author_user_id' => $author->id]);

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-webhook-secret')
            ->postJson('/api/telegram/webhook', [
                'update_id' => 1011,
                'message' => [
                    'message_id' => 60,
                    'caption' => 'Не сливает воду?',
                    'photo' => [['file_id' => 'small'], ['file_id' => 'large']],
                    'from' => ['id' => 778],
                    'chat' => ['id' => -100123, 'type' => 'supergroup'],
                ],
            ])
            ->assertOk();

        app(\App\Services\TelegramQuestionModerationService::class)->approve(60, $appliance->id, null);

        $question = Question::query()->latest('id')->firstOrFail();
        $this->assertStringContainsString('<img ', $question->description);
        $this->assertDatabaseHas('images', ['user_id' => $author->id, 'mime' => 'image/png']);
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
                    'text' => "Холодильник Samsung не морозит?\nНужна помощь мастера.",
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

    public function test_repeated_approve_does_not_create_a_second_question(): void
    {
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 903]])]);
        $author = User::factory()->create();
        $appliance = Appliance::create(['title' => 'Стиральные машины']);
        config(['services.telegram.author_user_id' => $author->id]);

        DB::table('telegram_question_messages')->insert([
            'telegram_chat_id' => -100123,
            'telegram_message_id' => 61,
            'telegram_update_id' => 1061,
            'telegram_user_id' => 778,
            'text' => 'Стиральная машина не запускается',
            'payload' => json_encode([
                'message' => [
                    'message_id' => 61,
                    'text' => 'Стиральная машина не запускается',
                ],
            ]),
            'status' => 'pending_moderation',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = app(\App\Services\TelegramQuestionModerationService::class);
        $first = $service->approve(61, $appliance->id, null);
        $second = $service->approve(61, $appliance->id, null);
        $questionId = (int) DB::table('telegram_question_messages')
            ->where('telegram_message_id', 61)
            ->value('question_id');

        $this->assertStringContainsString('Вопрос опубликован', $first);
        $this->assertSame("Вопрос уже опубликован: #{$questionId}.", $second);
        $this->assertDatabaseCount('questions', 1);
        $this->assertDatabaseHas('telegram_question_messages', [
            'telegram_message_id' => 61,
            'status' => 'published',
        ]);
    }

    public function test_author_can_mark_a_telegram_only_question_as_solved_and_award_the_answerer(): void
    {
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 904]])]);

        DB::table('telegram_question_messages')->insert([
            'telegram_chat_id' => -100123,
            'telegram_message_id' => 70,
            'telegram_update_id' => 1070,
            'telegram_user_id' => 778,
            'text' => 'Холодильник не охлаждает',
            'payload' => json_encode(['message' => ['message_id' => 70, 'text' => 'Холодильник не охлаждает']]),
            'status' => 'pending_moderation',
            'resolution_status' => 'open',
            'registered_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-webhook-secret')
            ->postJson('/api/telegram/webhook', [
                'update_id' => 1071,
                'message' => [
                    'message_id' => 71,
                    'text' => '/solved',
                    'from' => ['id' => 778],
                    'chat' => ['id' => -100123, 'type' => 'supergroup'],
                    'reply_to_message' => [
                        'message_id' => 72,
                        'text' => 'Проверьте датчик температуры.',
                        'from' => ['id' => 779, 'is_bot' => false],
                    ],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('telegram_question_messages', [
            'telegram_message_id' => 70,
            'resolution_status' => 'solved',
            'solution_telegram_user_id' => 779,
            'solution_telegram_message_id' => 72,
        ]);

        Http::assertSent(fn ($request): bool => str_ends_with($request->url(), '/setChatMemberTag')
            && $request['user_id'] === 779
            && $request['tag'] === 'Помощник');
        Http::assertSent(fn ($request): bool => str_ends_with($request->url(), '/sendMessage')
            && str_contains((string) $request['text'], 'Вопрос отмечен решённым'));
    }

    public function test_author_can_choose_a_solved_question_with_buttons_after_reacting_to_helper(): void
    {
        config(['services.telegram.source_chat_id' => '-100123']);
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 901]])]);

        foreach ([81, 82] as $messageId) {
            DB::table('telegram_question_messages')->insert([
                'telegram_chat_id' => -100123,
                'telegram_message_id' => $messageId,
                'telegram_update_id' => 2000 + $messageId,
                'telegram_user_id' => 778,
                'text' => "Холодильник {$messageId} не охлаждает?",
                'payload' => '{}',
                'status' => 'pending_moderation',
                'registered_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-webhook-secret')
            ->postJson('/api/telegram/webhook', [
                'update_id' => 2083,
                'message' => [
                    'message_id' => 84,
                    'text' => 'Проверьте датчик.',
                    'from' => ['id' => 779],
                    'chat' => ['id' => -100123, 'type' => 'supergroup'],
                ],
            ])->assertOk();

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-webhook-secret')
            ->postJson('/api/telegram/webhook', [
                'update_id' => 2085,
                'message_reaction' => [
                    'chat' => ['id' => -100123, 'type' => 'supergroup'],
                    'message_id' => 84,
                    'user' => ['id' => 778, 'is_bot' => false],
                    'date' => now()->timestamp,
                    'old_reaction' => [],
                    'new_reaction' => [['type' => 'emoji', 'emoji' => '👍']],
                ],
            ])->assertOk();

        $request = Http::recorded(fn ($request) => str_ends_with($request->url(), '/sendMessage'))->first()[0];
        $keyboard = json_decode($request['reply_markup'], true, 32, JSON_THROW_ON_ERROR);
        $this->assertCount(3, $keyboard['inline_keyboard']);
        $callbackData = $keyboard['inline_keyboard'][1][0]['callback_data'];

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-webhook-secret')
            ->postJson('/api/telegram/webhook', [
                'update_id' => 2084,
                'callback_query' => [
                    'id' => 'solve-test',
                    'data' => $callbackData,
                    'from' => ['id' => 778],
                    'message' => ['message_id' => 901, 'chat' => ['id' => -100123, 'type' => 'supergroup']],
                ],
            ])->assertOk();

        $this->assertDatabaseHas('telegram_solution_awards', [
            'telegram_user_id' => 779,
            'telegram_message_id' => 84,
        ]);
        $this->assertSame(1, DB::table('telegram_question_messages')->where('resolution_status', 'solved')->count());
    }

    public function test_thumbs_up_reaction_silently_solves_the_only_open_question(): void
    {
        config(['services.telegram.source_chat_id' => '-100123']);
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 901]])]);
        DB::table('telegram_question_messages')->insert([
            'telegram_chat_id' => -100123,
            'telegram_message_id' => 90,
            'telegram_update_id' => 2090,
            'telegram_user_id' => 778,
            'text' => 'Холодильник не охлаждает?',
            'payload' => '{}',
            'status' => 'pending_moderation',
            'registered_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-webhook-secret')
            ->postJson('/api/telegram/webhook', [
                'update_id' => 2091,
                'message' => [
                    'message_id' => 91,
                    'text' => 'Проверьте датчик.',
                    'from' => ['id' => 779],
                    'chat' => ['id' => -100123, 'type' => 'supergroup'],
                ],
            ])->assertOk();
        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'test-webhook-secret')
            ->postJson('/api/telegram/webhook', [
                'update_id' => 2092,
                'message_reaction' => [
                    'chat' => ['id' => -100123, 'type' => 'supergroup'],
                    'message_id' => 91,
                    'user' => ['id' => 778, 'is_bot' => false],
                    'date' => now()->timestamp,
                    'old_reaction' => [],
                    'new_reaction' => [['type' => 'emoji', 'emoji' => '👍']],
                ],
            ])->assertOk();
        $this->assertDatabaseHas('telegram_solution_awards', ['telegram_user_id' => 779, 'telegram_message_id' => 91]);
        Http::assertNotSent(fn ($request): bool => str_ends_with($request->url(), '/sendMessage'));
        Http::assertSent(fn ($request): bool => str_ends_with($request->url(), '/setMessageReaction')
            && $request['message_id'] === 91);
    }
}
