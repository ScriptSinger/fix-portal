<?php

namespace Tests\Feature;

use App\Actions\TelegramQuestions\ResolveQuestion;
use App\Jobs\SyncTelegramMemberTag;
use App\Services\TelegramBotApi;
use App\Services\TelegramReputation;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TelegramResolutionTest extends TestCase
{
    use DatabaseTransactions;

    private int $sequence;

    private int $authorId;

    private int $helperId;

    private int $chatId = -100900001;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sequence = random_int(1000000000, 2000000000);
        $this->authorId = ++$this->sequence;
        $this->helperId = ++$this->sequence;
        Bus::fake([SyncTelegramMemberTag::class]);
        Http::preventStrayRequests();
    }

    public function test_single_registered_question_is_resolved_and_award_records_its_provenance(): void
    {
        $id = $this->question();
        $message = $this->message();

        $response = app(TelegramReputation::class)->resolve($message);

        $this->assertSame('Вопрос отмечен решённым. Помощник — решений: 1.', $response);
        $this->assertDatabaseHas('telegram_solution_awards', [
            'telegram_question_message_id' => $id,
            'telegram_chat_id' => $this->chatId,
            'telegram_user_id' => $this->helperId,
            'telegram_message_id' => $message['reply_to_message']['message_id'],
            'command_message_id' => $message['message_id'],
        ]);
        $this->assertDatabaseHas('telegram_question_messages', [
            'id' => $id,
            'resolution_status' => 'solved',
            'solution_telegram_user_id' => $this->helperId,
            'solution_telegram_message_id' => $message['reply_to_message']['message_id'],
            'status' => 'pending_moderation',
            'question_id' => null,
        ]);
        $this->assertNotNull(DB::table('telegram_question_messages')->where('id', $id)->value('resolved_at'));
        Bus::assertDispatched(SyncTelegramMemberTag::class, fn ($job) => $job->telegramChatId === $this->chatId
            && $job->telegramUserId === $this->helperId && $job->afterCommit === true);
        Bus::assertDispatchedTimes(SyncTelegramMemberTag::class, 1);
        Http::assertNothingSent();
    }

    public function test_ambiguous_questions_require_an_id_and_the_wrapper_passes_it_to_the_action(): void
    {
        $first = $this->question();
        $second = $this->question();
        $message = $this->message();
        $reputation = app(TelegramReputation::class);

        $this->assertStringContainsString('/solved <ID>', $reputation->resolve($message));
        $this->assertSame(0, $reputation->countFor($this->helperId));
        Bus::assertNothingDispatched();
        $this->assertSolved($reputation->resolve($message, $first));
        $this->assertDatabaseHas('telegram_question_messages', ['id' => $first, 'resolution_status' => 'solved']);
        $this->assertDatabaseHas('telegram_question_messages', ['id' => $second, 'resolution_status' => 'open']);
    }

    #[DataProvider('publicationStatuses')]
    public function test_resolution_is_independent_of_publication_status(string $status): void
    {
        $id = $this->question(['status' => $status]);

        $this->assertSolved(ResolveQuestion::run($this->message(), $id));
        $this->assertDatabaseHas('telegram_question_messages', ['id' => $id, 'status' => $status, 'resolution_status' => 'solved']);
    }

    public static function publicationStatuses(): array
    {
        return [['rejected'], ['cancelled'], ['published'], ['pending_moderation']];
    }

    #[DataProvider('ineligibleQuestions')]
    public function test_ineligible_questions_are_never_resolved_even_with_an_explicit_id(array $attributes): void
    {
        $id = $this->question($attributes);
        $message = $this->message();

        foreach ([null, $id] as $questionId) {
            $this->assertStringContainsString('нет открытого', ResolveQuestion::run($message, $questionId));
        }

        $this->assertDatabaseMissing('telegram_solution_awards', ['telegram_question_message_id' => $id]);
        Bus::assertNothingDispatched();
    }

    public static function ineligibleQuestions(): array
    {
        return [
            'unconfirmed' => [['registered_at' => null]],
            'archived' => [['archived_at' => '2026-11-01 00:00:00']],
            'another chat' => [['telegram_chat_id' => -100900002]],
            'another thread' => [['telegram_thread_id' => 42]],
            'another author' => [['telegram_user_id' => 123]],
            'already solved' => [['resolution_status' => 'solved']],
        ];
    }

    public function test_ineligible_questions_do_not_make_the_eligible_question_ambiguous(): void
    {
        foreach (self::ineligibleQuestions() as [$attributes]) {
            $this->question($attributes);
        }
        $id = $this->question(['telegram_thread_id' => 0]);

        $this->assertSolved(ResolveQuestion::run($this->message()));
        $this->assertDatabaseHas('telegram_solution_awards', ['telegram_question_message_id' => $id]);
    }

    public function test_nonzero_thread_is_scoped_and_an_unknown_id_does_not_fall_back(): void
    {
        $general = $this->question();
        $topic = $this->question(['telegram_thread_id' => 42]);
        $message = $this->message(['message_thread_id' => 42]);

        $this->assertStringContainsString('нет открытого', ResolveQuestion::run($message, $general));
        $this->assertStringContainsString('нет открытого', ResolveQuestion::run($message, 0));
        $this->assertSolved(ResolveQuestion::run($message));
        $this->assertDatabaseHas('telegram_question_messages', ['id' => $topic, 'resolution_status' => 'solved']);
        $this->assertDatabaseHas('telegram_question_messages', ['id' => $general, 'resolution_status' => 'open']);
    }

    public function test_general_topic_uses_the_same_zero_thread_as_intake(): void
    {
        $id = $this->question();
        $this->assertSolved(ResolveQuestion::run($this->message([
            'message_thread_id' => 1,
            'reply_to_message' => ['message_thread_id' => 0],
        ]), $id));
    }

    #[DataProvider('invalidMessages')]
    public function test_invalid_answers_do_not_award_reputation(array $changes): void
    {
        $id = $this->question();

        $this->assertStringNotContainsString('Вопрос отмечен решённым.', ResolveQuestion::run($this->message($changes), $id));
        $this->assertDatabaseHas('telegram_question_messages', ['id' => $id, 'resolution_status' => 'open']);
        $this->assertDatabaseMissing('telegram_solution_awards', ['telegram_question_message_id' => $id]);
        Bus::assertNothingDispatched();
    }

    public static function invalidMessages(): array
    {
        return [
            'no reply' => [['reply_to_message' => null]],
            'no answer author' => [['reply_to_message' => ['from' => ['id' => 0]]]],
            'bot answer' => [['reply_to_message' => ['from' => ['is_bot' => true]]]],
            'anonymous answer' => [['reply_to_message' => ['sender_chat' => ['id' => -100900001]]]],
            'foreign reply chat' => [['reply_to_message' => ['chat' => ['id' => -100900002]]]],
            'foreign reply thread' => [['reply_to_message' => ['message_thread_id' => 42]]],
        ];
    }

    public function test_author_cannot_award_themselves(): void
    {
        $id = $this->question();
        $message = $this->message(['reply_to_message' => ['from' => ['id' => $this->authorId]]]);

        $this->assertStringContainsString('именно на сообщение участника', ResolveQuestion::run($message, $id));
        $this->assertSame(0, app(TelegramReputation::class)->countFor($this->authorId));
        $this->assertDatabaseHas('telegram_question_messages', ['id' => $id, 'resolution_status' => 'open']);
        Bus::assertNothingDispatched();
    }

    public function test_repeated_commands_cannot_award_the_same_answer_to_another_question(): void
    {
        $first = $this->question();
        $message = $this->message();
        $this->assertSolved(ResolveQuestion::run($message, $first));
        $second = $this->question();

        foreach ([null, $first, $second] as $id) {
            $this->assertStringNotContainsString('Вопрос отмечен решённым.', ResolveQuestion::run($message, $id));
        }
        $message['message_id'] = ++$this->sequence;
        $this->assertStringContainsString('уже учтены', ResolveQuestion::run($message, $second));
        $this->assertSame(1, app(TelegramReputation::class)->countFor($this->helperId));
        $this->assertDatabaseHas('telegram_question_messages', ['id' => $second, 'resolution_status' => 'open']);
        Bus::assertDispatchedTimes(SyncTelegramMemberTag::class, 1);
    }

    public function test_existing_question_award_prevents_another_award_even_if_record_is_reopened(): void
    {
        $id = $this->question();
        $this->assertSolved(ResolveQuestion::run($this->message(), $id));
        DB::table('telegram_question_messages')->where('id', $id)->update(['resolution_status' => 'open']);

        $this->assertStringContainsString('уже учтены', ResolveQuestion::run($this->message(), $id));
        $this->assertSame(1, app(TelegramReputation::class)->countFor($this->helperId));
        Bus::assertDispatchedTimes(SyncTelegramMemberTag::class, 1);
    }

    public function test_awards_and_answer_reuse_protection_survive_moderation_queue_record_removal(): void
    {
        $id = $this->question();
        $message = $this->message();
        $this->assertSolved(ResolveQuestion::run($message, $id));
        DB::table('telegram_question_messages')->where('id', $id)->delete();

        $reputation = app(TelegramReputation::class);
        $this->assertSame(1, $reputation->countFor($this->helperId));
        $this->assertSame('Помощник', $reputation->rankFor($this->helperId));
        $this->assertDatabaseHas('telegram_solution_awards', ['telegram_question_message_id' => $id]);
        $second = $this->question();
        $this->assertStringContainsString('уже учтены', ResolveQuestion::run($message, $second));
        $this->assertDatabaseHas('telegram_question_messages', ['id' => $second, 'resolution_status' => 'open']);
    }

    public function test_all_time_reputation_combines_chats_and_message_ids_are_unique_only_within_a_chat(): void
    {
        $message = $this->message();
        $answerMessageId = $message['reply_to_message']['message_id'];
        $reputation = app(TelegramReputation::class);
        $this->assertSame(0, $reputation->countFor($this->helperId));
        $this->assertNull($reputation->rankFor($this->helperId));

        for ($i = 0; $i < 10; $i++) {
            $chatId = $this->chatId - $i;
            $id = $this->question(['telegram_chat_id' => $chatId]);
            $this->assertSolved(ResolveQuestion::run($this->message([
                'chat' => ['id' => $chatId],
                'reply_to_message' => ['message_id' => $answerMessageId],
            ]), $id));
            // Counts are all-time, even for awards outside a reporting window.
            DB::table('telegram_solution_awards')->where('telegram_question_message_id', $id)->update(['created_at' => '2020-01-01 00:00:00']);
            $expectedRank = $i < 2 ? 'Помощник' : ($i < 9 ? 'Знаток' : 'Эксперт');
            $this->assertSame($i + 1, $reputation->countFor($this->helperId));
            $this->assertSame($expectedRank, $reputation->rankFor($this->helperId));
        }
    }

    public function test_tag_job_is_dispatched_only_after_the_enclosing_transaction_commits(): void
    {
        $id = $this->question();
        DB::beginTransaction();
        $this->assertSolved(ResolveQuestion::run($this->message(), $id));
        Bus::assertNothingDispatched();
        DB::commit();

        Bus::assertDispatchedTimes(SyncTelegramMemberTag::class, 1);
    }

    public function test_enclosing_rollback_removes_award_and_solution_and_discards_tag_job(): void
    {
        $id = $this->question();
        DB::beginTransaction();
        $this->assertSolved(ResolveQuestion::run($this->message(), $id));
        Bus::assertNothingDispatched();
        DB::rollBack();

        $this->assertDatabaseHas('telegram_question_messages', ['id' => $id, 'resolution_status' => 'open', 'resolved_at' => null]);
        $this->assertDatabaseMissing('telegram_solution_awards', ['telegram_question_message_id' => $id]);
        // A later commit must not release the rolled-back transaction's callback.
        DB::transaction(fn () => null);
        Bus::assertNothingDispatched();
    }

    public function test_failure_after_award_insert_rolls_back_the_entire_resolution(): void
    {
        $id = $this->question();
        DB::listen(function (QueryExecuted $query): void {
            if (preg_match('/^update ["`]?telegram_question_messages["`]? /i', $query->sql)) {
                throw new \RuntimeException('Simulated failure while marking solved');
            }
        });

        try {
            ResolveQuestion::run($this->message(), $id);
            $this->fail('Expected the resolution to fail');
        } catch (\RuntimeException $e) {
            $this->assertSame('Simulated failure while marking solved', $e->getMessage());
        }

        $this->assertDatabaseHas('telegram_question_messages', ['id' => $id, 'resolution_status' => 'open', 'resolved_at' => null]);
        $this->assertDatabaseMissing('telegram_solution_awards', ['telegram_question_message_id' => $id]);
        Bus::assertNothingDispatched();
    }

    public function test_database_enforces_both_award_uniqueness_constraints(): void
    {
        $id = $this->question();
        $this->assertSolved(ResolveQuestion::run($this->message(), $id));
        $award = (array) DB::table('telegram_solution_awards')->where('telegram_question_message_id', $id)->first();
        unset($award['id']);
        $other = $this->question();

        foreach ([['telegram_message_id' => ++$this->sequence], ['telegram_question_message_id' => $other]] as $changes) {
            try {
                DB::transaction(fn () => DB::table('telegram_solution_awards')->insert(array_replace($award, $changes)));
                $this->fail('Duplicate awards must be rejected by the database');
            } catch (UniqueConstraintViolationException $e) {
                $this->assertSame(1, app(TelegramReputation::class)->countFor($this->helperId));
            }
        }
    }

    public function test_tag_job_recomputes_the_current_rank_when_executed(): void
    {
        $job = new SyncTelegramMemberTag($this->chatId, $this->helperId);
        for ($i = 0; $i < 3; $i++) {
            $this->assertSolved(ResolveQuestion::run($this->message(), $this->question()));
        }
        $this->configureTelegram();
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => true])]);

        $job->handle(app(TelegramBotApi::class), app(TelegramReputation::class));

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/setChatMemberTag')
            && $request['chat_id'] === $this->chatId && $request['user_id'] === $this->helperId && $request['tag'] === 'Знаток');
    }

    public function test_tag_failure_can_be_retried_without_losing_or_duplicating_the_award(): void
    {
        $id = $this->question();
        $this->assertSolved(ResolveQuestion::run($this->message(), $id));
        $this->configureTelegram();
        Http::fakeSequence()->push(['ok' => false], 500)->push(['ok' => true, 'result' => true]);
        $job = new SyncTelegramMemberTag($this->chatId, $this->helperId);
        $this->assertSame(3, $job->tries);
        $this->assertSame([60, 300], $job->backoff());

        try {
            $job->handle(app(TelegramBotApi::class), app(TelegramReputation::class));
            $this->fail('API failures must reach the queue worker for retry');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Telegram API', $e->getMessage());
        }
        $job->handle(app(TelegramBotApi::class), app(TelegramReputation::class));

        $this->assertSame(1, app(TelegramReputation::class)->countFor($this->helperId));
        $this->assertDatabaseHas('telegram_question_messages', ['id' => $id, 'resolution_status' => 'solved']);
    }

    private function question(array $attributes = []): int
    {
        return DB::table('telegram_question_messages')->insertGetId(array_replace([
            'telegram_chat_id' => $this->chatId,
            'telegram_thread_id' => 0,
            'telegram_message_id' => ++$this->sequence,
            'telegram_update_id' => ++$this->sequence,
            'telegram_user_id' => $this->authorId,
            'payload' => '{}',
            'status' => 'pending_moderation',
            'resolution_status' => 'open',
            'registered_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));
    }

    private function message(array $changes = []): array
    {
        return array_replace_recursive([
            'message_id' => ++$this->sequence,
            'text' => '/solved',
            'chat' => ['id' => $this->chatId, 'type' => 'supergroup'],
            'from' => ['id' => $this->authorId, 'is_bot' => false],
            'reply_to_message' => [
                'message_id' => ++$this->sequence,
                'from' => ['id' => $this->helperId, 'is_bot' => false],
            ],
        ], $changes);
    }

    private function assertSolved(string $response): void
    {
        $this->assertStringStartsWith('Вопрос отмечен решённым.', $response);
    }

    private function configureTelegram(): void
    {
        config(['services.telegram.bot_token' => 'resolution-test-token', 'services.telegram.api_url' => 'https://api.telegram.org', 'services.telegram.proxy' => null]);
    }
}
