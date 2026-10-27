<?php

namespace Tests\Feature;

use App\Jobs\SendQuestionAnnouncement;
use App\Models\QuestionAnnouncement;
use App\Models\User;
use App\MoonShine\Resources\QuestionAnnouncementResource;
use App\Services\Announcements\QuestionAnnouncer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use MoonShine\Laravel\Models\MoonshineUser;
use MoonShine\Laravel\Models\MoonshineUserRole;
use Tests\Concerns\CreatesDiscussions;
use Tests\TestCase;

/** Раздел «Публикации вопросов» в админке: страницы открываются, повтор работает только для неудавшихся. */
class AnnouncementsAdminTest extends TestCase
{
    use CreatesDiscussions, DatabaseTransactions;

    private function admin(): MoonshineUser
    {
        $role = MoonshineUserRole::query()->first() ?? MoonshineUserRole::forceCreate(['name' => 'Admin']);

        return MoonshineUser::forceCreate([
            'moonshine_user_role_id' => $role->id,
            'name' => 'Тест',
            'email' => uniqid('admin') . '@example.invalid',
            'password' => bcrypt('secret-pass'),
        ]);
    }

    private function announcement(string $status): QuestionAnnouncement
    {
        $question = $this->makeQuestion(User::factory()->create());

        return QuestionAnnouncement::create([
            'question_id' => $question->id,
            'channel' => 'telegram',
            'status' => $status,
            'attempts' => 4,
            'last_error' => 'Telegram: HTTP request returned status code 502',
        ]);
    }

    public function test_admin_sees_the_list_with_questions_and_retry_for_failed(): void
    {
        $failed = $this->announcement(QuestionAnnouncement::FAILED);

        $this->actingAs($this->admin(), 'moonshine')
            ->get(app(QuestionAnnouncementResource::class)->getIndexPageUrl())
            ->assertOk()
            ->assertSee('Публикации вопросов')
            ->assertSee($failed->question->title)
            ->assertSee('Не удалось')
            ->assertSee('Повторить');
    }

    public function test_card_shows_the_last_error(): void
    {
        $failed = $this->announcement(QuestionAnnouncement::FAILED);

        $this->actingAs($this->admin(), 'moonshine')
            ->get(app(QuestionAnnouncementResource::class)->getDetailPageUrl($failed->id))
            ->assertOk()
            ->assertSee('status code 502');
    }

    public function test_guests_cannot_open_the_section(): void
    {
        $this->get(app(QuestionAnnouncementResource::class)->getIndexPageUrl())->assertRedirect();
    }

    public function test_retry_button_requeues_a_failed_announcement(): void
    {
        Queue::fake();
        $failed = $this->announcement(QuestionAnnouncement::FAILED);
        $resource = app(QuestionAnnouncementResource::class);
        $url = $resource->getAsyncMethodUrl('retry', params: ['resourceItem' => $failed->id], page: $resource->getIndexPage());

        $this->actingAs($this->admin(), 'moonshine')->get($url)->assertOk();

        $this->assertSame(QuestionAnnouncement::PENDING, $failed->refresh()->status);
        Queue::assertPushed(SendQuestionAnnouncement::class, 1);
    }

    public function test_retry_requeues_only_a_failed_announcement(): void
    {
        Queue::fake();
        $failed = $this->announcement(QuestionAnnouncement::FAILED);
        $sent = $this->announcement(QuestionAnnouncement::SENT);
        $announcer = app(QuestionAnnouncer::class);

        $this->assertTrue($announcer->retry($failed));
        $this->assertFalse($announcer->retry($sent));

        $failed->refresh();
        $this->assertSame(QuestionAnnouncement::PENDING, $failed->status);
        $this->assertSame(0, $failed->attempts);
        $this->assertNull($failed->last_error);
        $this->assertSame(QuestionAnnouncement::SENT, $sent->refresh()->status);
        Queue::assertPushed(SendQuestionAnnouncement::class, 1);
    }
}
