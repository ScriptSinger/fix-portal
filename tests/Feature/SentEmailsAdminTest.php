<?php

namespace Tests\Feature;

use App\Models\SentEmail;
use App\Models\User;
use App\MoonShine\Resources\SentEmailResource;
use App\Notifications\ActivityNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use MoonShine\Laravel\Models\MoonshineUser;
use MoonShine\Laravel\Models\MoonshineUserRole;
use Tests\TestCase;

/** Раздел «Письма» в админке: в журнале только уже отправленные письма, править там нечего. */
class SentEmailsAdminTest extends TestCase
{
    use DatabaseTransactions;

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

    private function notify(User $user, string $title, bool $emailed): void
    {
        $user->notify(new ActivityNotification([
            'kind' => ActivityNotification::ANSWER,
            'actor_id' => 1,
            'actor_name' => 'Мастер',
            'title' => $title,
            'in' => 'вопросе',
            'url' => 'https://example.invalid/q#c-1',
            'snippet' => 'Попробуйте заменить насос',
        ]));

        if ($emailed) {
            $user->notifications()->latest()->first()->forceFill(['emailed_at' => now()])->save();
        }
    }

    public function test_journal_lists_only_emailed_notifications(): void
    {
        $user = User::factory()->create();
        $this->notify($user, 'Письмо ушло', true);
        $this->notify($user, 'Письмо не уходило', false);

        $this->actingAs($this->admin(), 'moonshine')
            ->get(app(SentEmailResource::class)->getIndexPageUrl())
            ->assertOk()
            ->assertSee('Письма')
            ->assertSee('Ответили на вопрос')
            ->assertSee('Письмо ушло')
            ->assertDontSee('Письмо не уходило');
    }

    public function test_card_shows_the_answer_snippet(): void
    {
        $user = User::factory()->create();
        $this->notify($user, 'Письмо ушло', true);
        $mail = SentEmail::where('notifiable_id', $user->id)->firstOrFail();

        $this->actingAs($this->admin(), 'moonshine')
            ->get(app(SentEmailResource::class)->getDetailPageUrl($mail->id))
            ->assertOk()
            ->assertSee('Попробуйте заменить насос');
    }

    public function test_guests_cannot_open_the_section(): void
    {
        $this->get(app(SentEmailResource::class)->getIndexPageUrl())->assertRedirect();
    }
}
