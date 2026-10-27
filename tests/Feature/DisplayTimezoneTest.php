<?php

namespace Tests\Feature;

use App\Models\QuestionAnnouncement;
use App\Models\User;
use App\MoonShine\Resources\QuestionAnnouncementResource;
use App\Notifications\ActivityNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use MoonShine\Laravel\Models\MoonshineUser;
use MoonShine\Laravel\Models\MoonshineUserRole;
use Tests\Concerns\CreatesDiscussions;
use Tests\TestCase;

/** В базе время в UTC, людям оно показывается по config app.display_timezone (Уфа, UTC+5). */
class DisplayTimezoneTest extends TestCase
{
    use CreatesDiscussions, DatabaseTransactions;

    // 20:27 UTC 14 марта это 01:27 15 марта по Уфе (дата в прошлом: в кабинете сегодняшний день подписан «Сегодня»)
    private const UTC = '2026-03-14 20:27:00';

    public function test_display_timezone_is_ufa_by_default_and_storage_stays_utc(): void
    {
        $this->assertSame('Asia/Yekaterinburg', config('app.display_timezone'));
        $this->assertSame('UTC', config('app.timezone'));
    }

    public function test_dates_convert_for_display_without_touching_the_original(): void
    {
        foreach ([Carbon::parse(self::UTC, 'UTC'), \Illuminate\Support\Carbon::parse(self::UTC, 'UTC')] as $date) {
            $local = $date->inDisplayTimezone();

            $this->assertSame('15.03.2026 01:27', $local->format('d.m.Y H:i'));
            $this->assertSame('14.03.2026 20:27', $date->format('d.m.Y H:i'), 'исходная дата не меняется');
        }
    }

    public function test_admin_list_shows_local_time(): void
    {
        $question = $this->makeQuestion(User::factory()->create());
        QuestionAnnouncement::create(['question_id' => $question->id, 'channel' => 'telegram', 'status' => 'sent', 'sent_at' => self::UTC]);
        $role = MoonshineUserRole::query()->first() ?? MoonshineUserRole::forceCreate(['name' => 'Admin']);
        $admin = MoonshineUser::forceCreate(['moonshine_user_role_id' => $role->id, 'name' => 'Тест', 'email' => uniqid('a') . '@example.invalid', 'password' => bcrypt('secret-pass')]);

        $this->actingAs($admin, 'moonshine')
            ->get(app(QuestionAnnouncementResource::class)->getIndexPageUrl())
            ->assertOk()
            ->assertSee('15.03.2026 01:27')
            ->assertDontSee('14.03.2026 20:27');
    }

    public function test_question_page_shows_local_time_for_the_question_and_the_chat(): void
    {
        $question = $this->makeQuestion(User::factory()->create());
        DB::table('questions')->where('id', $question->id)->update(['created_at' => self::UTC]);
        $comment = $this->commentOn($question, User::factory()->create());
        DB::table('comments')->where('id', $comment->id)->update(['created_at' => '2026-03-14 21:05:00']);

        $page = $this->get($this->pageOf($question))->assertOk();

        // видимое время местное; в машинной разметке (JSON-LD, datetime) остаётся UTC с явным смещением
        $page->assertSee('15 марта 2026, 01:27')->assertDontSee('14 марта 2026, 20:27');
        $page->assertSee('15 марта 2026, 02:05')->assertDontSee('14 марта 2026, 21:05');
        $page->assertSee('2026-03-14T20:27:00+00:00', false);
    }

    public function test_cabinet_notifications_show_local_time_and_day(): void
    {
        $me = User::factory()->create();
        $me->notify(new ActivityNotification(['kind' => 'answer', 'actor_id' => 1, 'actor_name' => 'Помощник', 'title' => 'Тест', 'in' => 'вопросе', 'url' => '/questions/test']));
        DB::table('notifications')->where('notifiable_id', $me->id)->update(['created_at' => self::UTC]);

        $page = $this->actingAs($me)->get(route('cabinet.notifications'))->assertOk();

        $page->assertSee('01:27')->assertDontSee('20:27');
        $page->assertSee('15 марта 2026');
    }
}
