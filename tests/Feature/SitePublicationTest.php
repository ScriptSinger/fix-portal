<?php

namespace Tests\Feature;

use App\Actions\SitePublication\PublishQuestion;
use App\Actions\SitePublication\RejectPublication;
use App\Models\Appliance;
use App\Models\Question;
use App\Models\User;
use App\Services\Announcements\QuestionAnnouncer;
use App\Services\TelegramQuestionModerationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SitePublicationTest extends TestCase
{
    use DatabaseTransactions;

    private int $sequence;

    private Appliance $appliance;

    private int $httpTransactionLevel;

    public function test_deleting_moderation_record_preserves_group_question_and_confirmation(): void
    {
        $id = $this->message(['registered_at' => null, 'community_confirmation_status' => 'pending']);
        $this->publication($id);
        $registered = $this->message(['community_confirmation_status' => 'confirmed']);
        $this->publication($registered);
        $published = $this->message();
        $this->publication($published, ['status' => 'published']);
        $resource = app(\App\MoonShine\Resources\TelegramQuestionMessageResource::class);
        $resource->massDelete([$id, $registered, $published]);

        $this->assertDatabaseMissing('telegram_question_publications', ['telegram_question_message_id' => $id]);
        $this->assertDatabaseMissing('telegram_question_publications', ['telegram_question_message_id' => $registered]);
        $this->assertDatabaseHas('telegram_question_messages', ['id' => $registered, 'community_confirmation_status' => 'confirmed']);
        $this->assertDatabaseHas('telegram_question_publications', ['telegram_question_message_id' => $published, 'status' => 'published']);
        $this->assertDatabaseHas('telegram_question_messages', ['id' => $id, 'community_confirmation_status' => 'pending']);
        $record = $this->record($id);
        $this->assertTrue(\App\Actions\TelegramQuestions\ConfirmRegistration::run($id, 778, (int) $record->telegram_chat_id, true)['confirmed']);
    }

    public function test_existing_question_keeps_collected_album_when_submitted(): void
    {
        $id = $this->message();
        $record = $this->record($id);
        config(['services.telegram.source_chat_id' => (string) $record->telegram_chat_id,
            'services.telegram.moderation_chat_id' => (string) $record->telegram_chat_id,
            'services.telegram.moderator_ids' => [777]]);
        $photos = [[['file_id' => 'photo-one']], [['file_id' => 'photo-two']]];
        $this->mock(\App\Telegram\TelegramAlbum::class)->shouldReceive('collect')->once()->andReturn($photos);
        $message = [
            'from' => ['id' => 777], 'chat' => ['id' => $record->telegram_chat_id],
            'reply_to_message' => ['message_id' => $record->telegram_message_id, 'chat' => ['id' => $record->telegram_chat_id],
                'from' => ['id' => 778], 'text' => 'Вопрос?', 'media_group_id' => 'album-1'],
        ];
        $this->assertSame('Сообщение передано на модерацию.', \App\Actions\TelegramQuestions\MarkQuestion::run($record->telegram_update_id + 1, $message));
        $this->assertSame($photos, data_get(json_decode($this->record($id)->payload, true), 'message.album_photos'));
    }

    public function test_cancelled_registration_can_still_be_submitted_by_moderator(): void
    {
        $id = $this->message(['registered_at' => null, 'status' => 'awaiting_confirmation', 'community_confirmation_status' => 'pending']);
        $record = $this->record($id);
        config(['services.telegram.source_chat_id' => (string) $record->telegram_chat_id, 'services.telegram.moderator_ids' => [777]]);

        $result = \App\Actions\TelegramQuestions\ConfirmRegistration::run($id, 778, (int) $record->telegram_chat_id, false);
        $this->assertFalse($result['confirmed']);
        $this->assertSame('declined', $this->record($id)->community_confirmation_status);
        $this->assertSame('Сообщение передано на модерацию.', \App\Actions\SitePublication\SubmitForModeration::run($id, 777));
        $this->assertNull($this->record($id)->registered_at);
        $this->assertSame('pending_moderation', $this->state($id)->status);
    }

    public function test_moderation_does_not_end_pending_group_confirmation_or_lose_a_reply(): void
    {
        $id = $this->message(['registered_at' => null, 'status' => 'awaiting_confirmation', 'community_confirmation_status' => 'pending']);
        $record = $this->record($id);
        config(['services.telegram.source_chat_id' => (string) $record->telegram_chat_id, 'services.telegram.moderator_ids' => [777]]);
        \App\Actions\SitePublication\SubmitForModeration::run($id, 777);
        $payload = ['message' => [
            'message_id' => 999, 'chat' => ['id' => $record->telegram_chat_id],
            'from' => ['id' => 778], 'reply_to_message' => ['message_id' => $record->telegram_message_id],
            'text' => 'Дополнительная информация',
        ]];
        $continuation = app(\App\Services\TelegramQuestionIntake::class)->pendingReplyContinuation($payload);
        $this->assertSame($id, $continuation->id);
        app(TelegramQuestionModerationService::class)->attachCommunity($id, $payload);
        $this->assertStringContainsString('Дополнительная информация', $this->record($id)->text);
        $this->assertTrue(\App\Actions\TelegramQuestions\ConfirmRegistration::run($id, 778, (int) $record->telegram_chat_id, true)['confirmed']);
        $this->assertSame('pending_moderation', $this->state($id)->status);
    }

    public function test_publication_actions_cannot_create_a_moderation_record(): void
    {
        $id = $this->message();
        $this->assertSame('Вопрос не передан на модерацию.', PublishQuestion::run($id, $this->appliance->id, null));
        $this->assertSame('Вопрос не передан на модерацию.', RejectPublication::run($id, '', null));
        $this->assertDatabaseMissing('telegram_question_publications', ['telegram_question_message_id' => $id]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->sequence = random_int(1000000000, 2000000000);
        $this->appliance = Appliance::create(['title' => 'Publication test appliance']);
        $author = User::factory()->create();
        config([
            'services.telegram.bot_token' => 'publication-test-token',
            'services.telegram.api_url' => 'https://api.telegram.org',
            'services.telegram.author_user_id' => $author->id,
            'services.telegram.moderation_chat_id' => null,
            'services.telegram.moderation_thread_id' => null,
            'services.telegram.proxy' => null,
        ]);
        Storage::fake('public');
        Http::preventStrayRequests();
        $this->httpTransactionLevel = DB::transactionLevel();
        $this->fakeTelegram();
    }

    public function test_approval_uses_publication_truth_and_updates_all_ui_mirrors_once(): void
    {
        $id = $this->message(['status' => 'rejected', 'error_message' => 'stale mirror']);
        $this->publication($id, ['error_message' => 'old failure']);
        $record = $this->record($id);
        $service = app(TelegramQuestionModerationService::class);

        $this->assertStringContainsString('Вопрос опубликован:', $service->approve($id, $this->appliance->id, null));
        $publication = $this->state($id);
        $this->assertMirrors($id, 'published', (int) $publication->question_id, null);
        $this->assertSame($this->appliance->id, (int) $publication->appliance_id);
        $this->assertSame($this->appliance->id, (int) $this->record($id)->appliance_id);
        $this->assertSame(901, json_decode($this->record($id)->payload, true)['published_message_id']);
        $this->assertSame("Вопрос уже опубликован: #{$publication->question_id}.", $service->approve($id, $this->appliance->id, null));
        $this->assertSame(1, DB::table('telegram_moderation_actions')->where('action', 'approve')->whereJsonContains('parameters->message_id', $record->telegram_message_id)->count());
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request['message_thread_id'] === 42 && $request['reply_to_message_id'] === $record->telegram_message_id);
    }

    public function test_rejection_preserves_registration_resolution_and_awards(): void
    {
        $id = $this->message([
            'status' => 'failed',
            'resolution_status' => 'solved',
            'resolved_at' => now()->subMinute(),
            'solution_telegram_user_id' => 778,
            'solution_telegram_message_id' => 889,
        ]);
        $this->publication($id);
        DB::table('telegram_solution_awards')->insert([
            'telegram_question_message_id' => $id,
            'telegram_chat_id' => $this->record($id)->telegram_chat_id,
            'telegram_user_id' => 778,
            'telegram_message_id' => 889,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $before = $this->record($id);
        $award = DB::table('telegram_solution_awards')->where('telegram_question_message_id', $id)->first();

        $this->assertSame('Вопрос отклонён.', app(TelegramQuestionModerationService::class)->reject($id, 'Не для сайта', 777));
        $this->assertMirrors($id, 'rejected', null, 'Не для сайта');
        $after = $this->record($id);
        foreach (['registered_at', 'resolution_status', 'resolved_at', 'solution_telegram_user_id', 'solution_telegram_message_id'] as $field) {
            $this->assertSame($before->$field, $after->$field);
        }
        $this->assertEquals($award, DB::table('telegram_solution_awards')->where('telegram_question_message_id', $id)->first());
        $this->assertSame('Нельзя отклонить: текущий статус rejected.', RejectPublication::run($id, '', null));
        Http::assertNothingSent();
    }

    public function test_rejected_publication_cannot_be_approved_through_a_stale_pending_mirror(): void
    {
        $id = $this->message();
        $this->publication($id, ['status' => 'rejected']);
        $this->assertSame('Нельзя опубликовать: текущий статус rejected.', PublishQuestion::run($id, $this->appliance->id, null));
        Http::assertNothingSent();
    }

    public function test_failed_photo_import_can_be_retried_and_clears_both_errors(): void
    {
        $id = $this->message([], ['photo' => [['file_id' => 'large']]]);
        $this->publication($id);
        $this->fakeTelegram(static fn () => Http::response(['ok' => false, 'description' => 'download unavailable'], 500));

        $this->assertStringContainsString('Публикация остановлена:', PublishQuestion::run($id, $this->appliance->id, null));
        $this->assertMirrors($id, 'failed', null, $this->state($id)->error_message);
        $this->assertStringContainsString('download unavailable', $this->state($id)->error_message);
        $this->assertSame(0, DB::table('images')->where('user_id', config('services.telegram.author_user_id'))->count());

        $this->fakeTelegram();
        $this->assertStringContainsString('Вопрос опубликован:', PublishQuestion::run($id, $this->appliance->id, null));
        $this->assertMirrors($id, 'published', (int) $this->state($id)->question_id, null);
        $question = Question::findOrFail($this->state($id)->question_id);
        $this->assertStringContainsString('<img ', $question->description);
        $this->assertSame(1, DB::table('images')->where('user_id', $question->user_id)->count());
    }

    public function test_album_and_attached_photos_are_imported_once_per_file_id(): void
    {
        $id = $this->message([], [
            'photo' => [['file_id' => 'first']],
            'album_photos' => [[['file_id' => 'first']], [['file_id' => 'second']]],
        ]);
        $record = $this->record($id);
        $payload = json_decode($record->payload, true);
        $payload['attached_messages'] = [['caption' => 'Фото детали', 'photo' => [['file_id' => 'third']]]];
        DB::table('telegram_question_messages')->where('id', $id)->update(['payload' => json_encode($payload), 'text' => $record->text."\n\nФото детали"]);
        $this->publication($id);

        PublishQuestion::run($id, $this->appliance->id, null);

        $question = Question::findOrFail($this->state($id)->question_id);
        $this->assertSame(3, substr_count($question->description, '<img '));
        $this->assertStringContainsString('Фото детали', $question->description);
        $this->assertSame(1, substr_count($question->description, 'Фото детали'));
        Http::assertSentCount(7);
    }

    public function test_notification_failure_keeps_publication_and_error_in_both_records(): void
    {
        $id = $this->message();
        $this->publication($id);
        $this->fakeTelegram(static fn () => Http::response(['ok' => false, 'description' => 'chat unavailable'], 403));

        $this->assertStringContainsString('Вопрос опубликован:', PublishQuestion::run($id, $this->appliance->id, null));
        $publication = $this->state($id);
        $this->assertMirrors($id, 'published', (int) $publication->question_id, $publication->error_message);
        $this->assertStringContainsString('chat unavailable', $publication->error_message);
        $this->assertNotNull(Question::find($publication->question_id));
    }

    public function test_notification_merges_reply_id_with_concurrent_payload_changes(): void
    {
        $id = $this->message();
        $this->publication($id);
        $this->fakeTelegram(function () use ($id) {
            $payload = json_decode($this->record($id)->payload, true);
            $payload['attached_message_ids'] = [888];
            DB::table('telegram_question_messages')->where('id', $id)->update(['payload' => json_encode($payload)]);

            return Http::response(['ok' => true, 'result' => ['message_id' => 902]]);
        });

        PublishQuestion::run($id, $this->appliance->id, null);

        $payload = json_decode($this->record($id)->payload, true);
        $this->assertSame([888], $payload['attached_message_ids']);
        $this->assertSame(902, $payload['published_message_id']);
    }

    public function test_late_download_failure_cannot_overwrite_a_rejection(): void
    {
        $id = $this->message([], ['photo' => [['file_id' => 'large']]]);
        $this->publication($id);
        $this->fakeTelegram(function () use ($id) {
            RejectPublication::run($id, 'Concurrent rejection', null);

            return Http::response(['ok' => false], 500);
        });

        $this->assertSame('Нельзя опубликовать: текущий статус rejected.', PublishQuestion::run($id, $this->appliance->id, null));
        $this->assertMirrors($id, 'rejected', null, 'Concurrent rejection');
    }

    public function test_changed_payload_during_download_requires_a_fresh_attempt(): void
    {
        $id = $this->message([], ['photo' => [['file_id' => 'large']]]);
        $this->publication($id);
        $this->fakeTelegram(function ($request) use ($id) {
            DB::table('telegram_question_messages')->where('id', $id)->update(['text' => 'Новые сведения']);

            return $this->telegramResponse($request);
        });

        $this->assertStringContainsString('Вопрос изменился', PublishQuestion::run($id, $this->appliance->id, null));
        $this->assertMirrors($id, 'pending_moderation', null, null);
        $this->assertSame(0, DB::table('images')->where('user_id', config('services.telegram.author_user_id'))->count());
    }

    public function test_actions_target_database_ids_even_when_telegram_message_ids_collide(): void
    {
        $first = $this->message(['telegram_message_id' => 100]);
        $second = $this->message(['telegram_message_id' => 100]);
        $this->publication($first);
        $this->publication($second);

        $service = app(TelegramQuestionModerationService::class);
        $this->assertSame('Preview отправлен повторно.', $service->retry($second, null));
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/sendMessage')
            && $request['chat_id'] === (int) $this->record($second)->telegram_chat_id);
        $this->assertSame('Вопрос отклонён.', $service->reject($first, 'Причина', null));
        $this->assertStringContainsString('Вопрос опубликован:', $service->approve($second, $this->appliance->id, null));

        $this->assertMirrors($first, 'rejected', null, 'Причина');
        $this->assertMirrors($second, 'published', (int) $this->state($second)->question_id, null);
    }

    public function test_append_uses_authoritative_question_id_and_deduplicates_messages(): void
    {
        $id = $this->message();
        $this->publication($id);
        PublishQuestion::run($id, $this->appliance->id, null);
        $questionId = (int) $this->state($id)->question_id;
        DB::table('telegram_question_messages')->where('id', $id)->update(['status' => 'pending_moderation', 'question_id' => null]);
        $this->mock(QuestionAnnouncer::class)->shouldReceive('refresh')->once()->withArgs(fn ($question) => $question->id === $questionId);
        $payload = ['message' => ['message_id' => 998, 'text' => '<b>Дополнение</b>']];
        $service = app(TelegramQuestionModerationService::class);

        $this->assertSame('Сообщение и вложения добавлены к опубликованному вопросу.', $service->attach($id, $payload, null));
        $this->assertSame('Это сообщение уже прикреплено к вопросу.', $service->attach($id, $payload, null));
        $this->assertSame(1, substr_count(Question::findOrFail($questionId)->description, '&lt;b&gt;Дополнение&lt;/b&gt;'));
    }

    public function test_outer_rollback_removes_publication_and_never_notifies_telegram(): void
    {
        $id = $this->message();
        $this->publication($id);
        DB::beginTransaction();
        try {
            PublishQuestion::run($id, $this->appliance->id, null);
            $questionId = $this->state($id)->question_id;
            Http::assertNothingSent();
        } finally {
            DB::rollBack();
        }

        $this->assertMirrors($id, 'pending_moderation', null, null);
        $this->assertNull(Question::find($questionId));
        Http::assertNothingSent();
    }

    public function test_preview_retry_and_pending_attachment_apis_remain_available(): void
    {
        $id = $this->message();
        $this->publication($id);
        $record = $this->record($id);
        $service = app(TelegramQuestionModerationService::class);
        $this->assertTrue($service->preview($record->telegram_update_id, json_decode($record->payload, true)));
        $this->assertSame('Preview отправлен повторно.', $service->retry($id, null));
        $this->assertStringContainsString('будет опубликовано вместе с ним', $service->attach($id, ['message' => ['message_id' => 999, 'text' => 'Продолжение']], null));
        $this->assertStringContainsString('Продолжение', $this->record($id)->text);
        Http::assertSentCount(2);
    }

    private function message(array $attributes = [], array $message = []): int
    {
        $sequence = ++$this->sequence;
        $message = array_replace([
            'message_id' => $attributes['telegram_message_id'] ?? $sequence,
            'message_thread_id' => 42,
            'chat' => ['id' => -$sequence],
            'text' => "Холодильник не работает?\nНужна помощь.",
        ], $message);

        return DB::table('telegram_question_messages')->insertGetId(array_replace([
            'telegram_chat_id' => -$sequence,
            'telegram_message_id' => $sequence,
            'telegram_update_id' => $sequence,
            'telegram_user_id' => 778,
            'text' => $message['text'],
            'payload' => json_encode(['message' => $message]),
            'status' => 'pending_moderation',
            'registered_at' => now()->subHour(),
            'resolution_status' => 'open',
            'created_at' => now(), 'updated_at' => now(),
        ], $attributes));
    }

    private function publication(int $id, array $attributes = []): void
    {
        DB::table('telegram_question_publications')->insert(array_replace([
            'telegram_question_message_id' => $id,
            'status' => 'pending_moderation',
            'created_at' => now(), 'updated_at' => now(),
        ], $attributes));
    }

    private function record(int $id): object
    {
        return DB::table('telegram_question_messages')->where('id', $id)->first();
    }

    private function state(int $id): object
    {
        return DB::table('telegram_question_publications')->where('telegram_question_message_id', $id)->first();
    }

    private function assertMirrors(int $id, string $status, ?int $questionId, ?string $error): void
    {
        foreach ([$this->record($id), $this->state($id)] as $record) {
            $this->assertSame($status, $record->status);
            $this->assertSame($questionId, $record->question_id === null ? null : (int) $record->question_id);
            $this->assertSame($error, $record->error_message);
        }
    }

    private function fakeTelegram(?callable $response = null): void
    {
        // Rebinding clears any earlier catch-all fake in the same test.
        Http::swap(new Factory);
        Http::preventStrayRequests();
        Http::fake(function ($request) use ($response) {
            $this->assertSame($this->httpTransactionLevel, DB::transactionLevel(), 'Network I/O must run outside action transactions.');

            return $response ? $response($request) : $this->telegramResponse($request);
        });
    }

    private function telegramResponse($request)
    {
        if (str_contains($request->url(), '/getFile')) {
            return Http::response(['ok' => true, 'result' => ['file_path' => 'photos/test.png']]);
        }
        if (str_contains($request->url(), '/file/bot')) {
            return Http::response(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='), 200, ['Content-Type' => 'image/png']);
        }

        return Http::response(['ok' => true, 'result' => ['message_id' => 901]]);
    }
}
