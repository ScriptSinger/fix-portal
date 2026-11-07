<?php

namespace Tests\Feature;

use App\Jobs\SendTelegramQuestionReminder;
use App\Services\TelegramBotApi;
use App\Services\TelegramCommunityDigest;
use App\Services\TelegramQuestionReminders;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TelegramCommunityRemindersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // This component's schema contract is isolated from the shared development DB.
        config([
            'database.default' => 'telegram_reminders_test',
            'database.connections.telegram_reminders_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'services.telegram.bot_token' => 'test-token',
            'services.telegram.api_url' => 'https://api.telegram.org',
            'services.telegram.proxy' => null,
            'services.telegram.source_chat_id' => '-100123',
            'services.telegram.source_thread_id' => null,
            'queue.default' => 'sync',
        ]);
        $this->travelTo(now()->startOfSecond());
        Http::preventStrayRequests();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 777]])]);
        Queue::fake();

        foreach ([
            '2026_11_02_140000_create_telegram_question_messages_table.php',
            '2026_11_07_180000_add_telegram_resolution_fields.php',
            '2026_11_07_190000_separate_telegram_community_and_publication.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    protected function tearDown(): void
    {
        $this->travelBack();
        DB::purge('telegram_reminders_test');
        parent::tearDown();
    }

    private function question(array $overrides = []): int
    {
        $sequence = (int) DB::table('telegram_question_messages')->max('id') + 1;

        return DB::table('telegram_question_messages')->insertGetId($overrides + [
            'telegram_chat_id' => -100123,
            'telegram_message_id' => 54 + $sequence,
            'telegram_update_id' => $sequence,
            'payload' => '{}',
            'registered_at' => now()->subHours(72),
            'created_at' => now()->subYear(),
            'updated_at' => now(),
        ]);
    }

    private function send(int $reminderId): void
    {
        (new SendTelegramQuestionReminder($reminderId))->handle(
            app(TelegramBotApi::class), app(TelegramQuestionReminders::class),
        );
    }

    private function award(int $userId, int $chatId = -100123): void
    {
        $questionId = $this->question(['resolution_status' => 'solved']);
        DB::table('telegram_solution_awards')->insert([
            'telegram_question_message_id' => $questionId,
            'telegram_chat_id' => $chatId,
            'telegram_user_id' => $userId,
            'telegram_message_id' => 1000 + $questionId * 2,
            'command_message_id' => 1001 + $questionId * 2,
            'created_at' => now()->subYears(2),
            'updated_at' => now()->subYears(2),
        ]);
    }

    public function test_registration_age_and_state_control_eligibility_independently_of_publication(): void
    {
        $expected = [];
        foreach (['pending_moderation', 'published', 'rejected', 'cancelled'] as $status) {
            $expected[] = $this->question(['status' => $status]);
        }
        $expected[] = $this->question(['registered_at' => now()->subYears(2)]);
        $this->question(['registered_at' => now()->subHours(72)->addSecond()]);
        $this->question(['registered_at' => null]);
        $this->question(['resolution_status' => 'solved']);
        $this->question(['archived_at' => now()]);
        $this->question(['telegram_chat_id' => -100999]);

        $this->artisan('telegram:questions-remind', ['--limit' => 20])->assertSuccessful();
        $this->artisan('telegram:questions-remind', ['--limit' => 20])->assertSuccessful();

        $this->assertEqualsCanonicalizing($expected, DB::table('telegram_question_reminders')->pluck('telegram_question_message_id')->all());
        Queue::assertPushed(SendTelegramQuestionReminder::class, count($expected));
        Http::assertNothingSent();
    }

    public function test_dry_run_and_limit_include_only_due_rows_and_never_reserve_on_dry_run(): void
    {
        $pending = $this->question(['registered_at' => now()->subDays(12)]);
        app(TelegramQuestionReminders::class)->reserve($pending);
        $oldest = $this->question(['registered_at' => now()->subDays(10)]);
        $next = $this->question(['registered_at' => now()->subDays(9)]);
        $third = $this->question();
        $fourth = $this->question();

        $this->artisan('telegram:questions-remind', ['--dry-run' => true, '--limit' => 2])
            ->expectsOutput('Напомнили бы: #'.$oldest)
            ->expectsOutput('Напомнили бы: #'.$next)
            ->doesntExpectOutput('Напомнили бы: #'.$third)->assertSuccessful();
        $this->assertDatabaseCount('telegram_question_reminders', 1);
        Queue::assertNothingPushed();

        $this->artisan('telegram:questions-remind', ['--limit' => 0])->assertSuccessful();
        $this->artisan('telegram:questions-remind', ['--limit' => -1])->assertSuccessful();
        Queue::assertNothingPushed();

        $this->artisan('telegram:questions-remind')->assertSuccessful();
        Queue::assertPushed(SendTelegramQuestionReminder::class, 3);
        $this->assertDatabaseMissing('telegram_question_reminders', ['telegram_question_message_id' => $fourth]);
        Http::assertNothingSent();
    }

    public function test_three_successful_reminders_are_spaced_from_actual_send_time(): void
    {
        $questionId = $this->question(['registered_at' => now()->subYears(2)]);
        $service = app(TelegramQuestionReminders::class);

        for ($number = 1; $number <= 3; $number++) {
            $reminderId = $service->reserve($questionId);
            $this->assertNotNull($reminderId);
            $this->assertNull($service->reserve($questionId), 'Pending reservations deduplicate.');
            $this->travel(6)->hours(); // Delayed worker must not shorten the next interval.
            $this->send($reminderId);
            $this->send($reminderId); // Duplicate delivery must not send twice.
            $this->assertDatabaseHas('telegram_question_reminders', [
                'id' => $reminderId, 'number' => $number, 'status' => 'sent',
                'telegram_message_id' => 777, 'sent_at' => now()->toDateTimeString(),
            ]);
            $this->travel(72 * 3600 - 1)->seconds();
            $this->assertNull($service->reserve($questionId));
            $this->assertSame(0, $service->due()->count());
            $this->travel(1)->seconds();
        }

        $this->assertNull($service->reserve($questionId));
        $this->assertSame(0, $service->due()->count());
        $this->assertDatabaseCount('telegram_question_reminders', 3);
        Http::assertSentCount(3);
    }

    public function test_job_replies_to_original_message_in_its_topic_with_reaction_instructions(): void
    {
        $id = $this->question(['telegram_thread_id' => 42, 'status' => 'rejected']);
        $reminderId = app(TelegramQuestionReminders::class)->reserve($id);
        $this->send($reminderId);

        Http::assertSent(fn (Request $request) => $request->url() === 'https://api.telegram.org/bottest-token/sendMessage'
            && $request['chat_id'] === -100123
            && $request['message_thread_id'] === 42
            && $request['reply_parameters'] === ['message_id' => 55]
            && str_contains($request['text'], 'поставьте 🤝')
            && ! isset($request['reply_markup']));
    }

    public function test_job_omits_topic_for_non_topic_questions(): void
    {
        $id = app(TelegramQuestionReminders::class)->reserve($this->question());
        $this->send($id);
        Http::assertSent(fn (Request $request) => ! isset($request['message_thread_id']));
    }

    public function test_job_rechecks_question_state_after_queueing(): void
    {
        foreach ([
            ['resolution_status' => 'solved'],
            ['archived_at' => now()],
            ['registered_at' => null],
            ['registered_at' => now()],
            ['telegram_chat_id' => -100999],
        ] as $change) {
            $questionId = $this->question();
            $reminderId = app(TelegramQuestionReminders::class)->reserve($questionId);
            DB::table('telegram_question_messages')->where('id', $questionId)->update($change);
            $this->send($reminderId);
            $this->assertDatabaseHas('telegram_question_reminders', ['id' => $reminderId, 'status' => 'cancelled']);
        }

        $questionId = $this->question();
        $reminderId = app(TelegramQuestionReminders::class)->reserve($questionId);
        DB::table('telegram_question_messages')->where('id', $questionId)->delete();
        $this->send($reminderId);
        $this->send(999999);
        Http::assertNothingSent();
    }

    public function test_job_rechecks_current_source_configuration_and_bot_availability(): void
    {
        foreach ([
            ['services.telegram.source_chat_id' => '-100999'],
            ['services.telegram.source_chat_id' => null],
            ['services.telegram.source_thread_id' => 99],
            ['services.telegram.bot_token' => ''],
        ] as $change) {
            config([
                'services.telegram.source_chat_id' => '-100123',
                'services.telegram.source_thread_id' => null,
                'services.telegram.bot_token' => 'test-token',
            ]);
            $reminderId = app(TelegramQuestionReminders::class)->reserve($this->question());
            config($change);
            $this->send($reminderId);
            $this->assertDatabaseHas('telegram_question_reminders', ['id' => $reminderId, 'status' => 'cancelled']);
        }
        Http::assertNothingSent();
    }

    public function test_disabled_configuration_and_configured_topic_are_respected_by_command(): void
    {
        $id = $this->question(['telegram_thread_id' => 42]);
        $this->question(['telegram_thread_id' => 43]);
        $this->question();
        config(['services.telegram.source_chat_id' => null]);
        $this->artisan('telegram:questions-remind')->assertSuccessful();
        config(['services.telegram.source_chat_id' => '-100123', 'services.telegram.bot_token' => '']);
        $this->artisan('telegram:questions-remind')->assertSuccessful();
        $this->assertDatabaseCount('telegram_question_reminders', 0);

        config(['services.telegram.bot_token' => 'test-token', 'services.telegram.source_thread_id' => 42]);
        $this->artisan('telegram:questions-remind')->assertSuccessful();
        $this->assertDatabaseHas('telegram_question_reminders', ['telegram_question_message_id' => $id]);
        Queue::assertPushed(SendTelegramQuestionReminder::class, 1);
    }

    public function test_failed_send_is_persisted_and_command_retries_the_same_number_once(): void
    {
        Http::swap(new Factory);
        Http::preventStrayRequests();
        Http::fake(['api.telegram.org/*' => Http::sequence()
            ->push(['ok' => false, 'description' => 'Temporary failure'], 503)
            ->push(['ok' => true, 'result' => ['message_id' => 888]])]);
        $questionId = $this->question();
        $reminderId = app(TelegramQuestionReminders::class)->reserve($questionId);
        $caught = false;
        try {
            $this->send($reminderId);
        } catch (\RuntimeException $exception) {
            $caught = true;
            $this->assertStringContainsString('Temporary failure', $exception->getMessage());
        }
        $this->assertTrue($caught, 'The queue worker must observe the failed request.');
        $this->assertDatabaseHas('telegram_question_reminders', [
            'id' => $reminderId, 'status' => 'failed', 'number' => 1, 'sent_at' => null,
        ]);
        $this->assertNotNull(DB::table('telegram_question_reminders')->where('id', $reminderId)->value('last_error'));
        $this->artisan('telegram:questions-remind')->assertSuccessful();
        $this->artisan('telegram:questions-remind')->assertSuccessful();
        Queue::assertPushed(SendTelegramQuestionReminder::class, 1);
        Queue::assertPushed(SendTelegramQuestionReminder::class, fn ($job) => $job->reminderId === $reminderId);
        $this->send($reminderId);
        $this->assertDatabaseCount('telegram_question_reminders', 1);
        $this->assertDatabaseHas('telegram_question_reminders', [
            'id' => $reminderId, 'status' => 'sent', 'last_error' => null, 'telegram_message_id' => 888,
        ]);
    }

    public function test_cancelled_reservation_can_be_reused_if_question_becomes_eligible_again(): void
    {
        $questionId = $this->question();
        $service = app(TelegramQuestionReminders::class);
        $reminderId = $service->reserve($questionId);
        DB::table('telegram_question_messages')->where('id', $questionId)->update(['archived_at' => now()]);
        $this->send($reminderId);
        DB::table('telegram_question_messages')->where('id', $questionId)->update(['archived_at' => null]);
        $this->assertSame($reminderId, $service->reserve($questionId));
        $this->send($reminderId);
        Http::assertSentCount(1);
    }

    public function test_worker_rechecks_spacing_before_http(): void
    {
        $questionId = $this->question();
        $first = app(TelegramQuestionReminders::class)->reserve($questionId);
        $this->send($first);
        $this->travel(72)->hours();
        $second = app(TelegramQuestionReminders::class)->reserve($questionId);
        DB::table('telegram_question_reminders')->where('id', $first)->update(['sent_at' => now()]);
        $this->send($second);
        $this->assertDatabaseHas('telegram_question_reminders', ['id' => $second, 'status' => 'cancelled']);
        Http::assertSentCount(1);
    }

    public function test_failed_hook_does_not_overwrite_successful_delivery(): void
    {
        $reminderId = app(TelegramQuestionReminders::class)->reserve($this->question());
        $job = new SendTelegramQuestionReminder($reminderId);
        $job->failed(new \RuntimeException('Worker failed'));
        $this->assertDatabaseHas('telegram_question_reminders', ['id' => $reminderId, 'status' => 'failed']);
        DB::table('telegram_question_reminders')->where('id', $reminderId)->update(['status' => 'sent']);
        $job->failed(new \RuntimeException('Late failure'));
        $this->assertDatabaseHas('telegram_question_reminders', ['id' => $reminderId, 'status' => 'sent']);
    }

    public function test_digest_counts_registered_open_questions_in_source_chat_regardless_of_age_or_publication(): void
    {
        $this->question(['registered_at' => now(), 'status' => 'rejected']);
        $this->question(['telegram_thread_id' => 42]);
        $this->question(['registered_at' => null]);
        $this->question(['resolution_status' => 'solved']);
        $this->question(['archived_at' => now()]);
        $this->question(['telegram_chat_id' => -100999]);
        config(['services.telegram.source_thread_id' => 42]);

        $this->assertSame('Открытых вопросов в Telegram: 2.', app(TelegramCommunityDigest::class)->text());
        Http::assertNothingSent();
        Queue::assertNothingPushed();
    }

    public function test_digest_uses_top_ten_global_all_time_ledger_totals_and_numeric_identity(): void
    {
        foreach ([900 => 10, 800 => 3, 700 => 1] as $userId => $count) {
            for ($i = 0; $i < $count; $i++) {
                $this->award($userId, -100999);
            }
        }
        for ($userId = 1000; $userId <= 1007; $userId++) {
            $this->award($userId);
        }
        // The ledger remains authoritative even if a source question is reopened/archived.
        DB::table('telegram_question_messages')->where('id', 1)->update(['resolution_status' => 'open', 'archived_at' => now()]);
        $text = app(TelegramCommunityDigest::class)->text();

        $this->assertStringContainsString('Открытых вопросов в Telegram: 0.', $text);
        $this->assertStringContainsString('1. <a href="tg://user?id=900">ID 900</a> — карма: 10, Магос', $text);
        $this->assertStringContainsString('2. <a href="tg://user?id=800">ID 800</a> — карма: 3, Техножрец', $text);
        $this->assertStringContainsString('3. <a href="tg://user?id=700">ID 700</a> — карма: 1, Техноадепт', $text);
        $this->assertSame(10, substr_count($text, 'tg://user?id='));
        $this->assertStringNotContainsString('ID 1007', $text);
        $this->assertStringNotContainsString('\\n', $text);
        Http::assertNothingSent();
    }

    public function test_digest_is_null_without_content_or_source_configuration(): void
    {
        $this->assertNull(app(TelegramCommunityDigest::class)->text());
        $this->award(123);
        $this->assertNotNull(app(TelegramCommunityDigest::class)->text());
        config(['services.telegram.source_chat_id' => null]);
        $this->assertNull(app(TelegramCommunityDigest::class)->text());
    }
}
