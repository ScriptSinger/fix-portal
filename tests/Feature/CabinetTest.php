<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\CreatesDiscussions;
use Tests\TestCase;

/** Личный кабинет: доступ, уведомления, списки и профиль. */
class CabinetTest extends TestCase
{
    use CreatesDiscussions, DatabaseTransactions;

    private function notify(User $user, array $extra = []): string
    {
        $user->notify(new ActivityNotification($extra + [
            'kind' => 'reply', 'actor_id' => User::factory()->create()->id, 'actor_name' => 'Помощник', 'title' => 'Тестовая страница',
            'in' => 'вопросе', 'url' => '/questions/test#r-1', 'target_id' => 'r-1', 'snippet' => 'Текст ответа',
        ]));

        return $user->notifications()->latest()->first()->id;
    }

    public function test_cabinet_pages_require_a_login(): void
    {
        foreach (['cabinet.index', 'cabinet.notifications', 'cabinet.questions', 'cabinet.answers', 'profile.edit'] as $name) {
            $this->get(route($name))->assertRedirect(route('login'));
        }
    }

    public function test_cabinet_pages_open_for_a_user(): void
    {
        $this->actingAs(User::factory()->create());

        foreach (['cabinet.index', 'cabinet.notifications', 'cabinet.questions', 'cabinet.answers', 'profile.edit'] as $name) {
            $this->get(route($name))->assertOk();
        }
    }

    public function test_overview_counts_questions_answers_and_solutions(): void
    {
        $me = User::factory()->create();
        $question = $this->makeQuestion($me);
        $other = $this->makeQuestion(User::factory()->create());
        $answer = $this->commentOn($other, $me);
        $other->forceFill(['solved_at' => now(), 'solution_type' => 'comment', 'solution_id' => $answer->id])->save();

        $this->actingAs($me)->get(route('cabinet.index'))->assertOk()->assertSee($question->title)->assertSee('Помощник')->assertSee('Решений');
    }

    public function test_notification_opens_the_target_and_is_marked_read(): void
    {
        $me = User::factory()->create();
        $id = $this->notify($me);

        $this->actingAs($me)->get(route('cabinet.notifications'))->assertOk()->assertSee('Помощник')->assertSee('ответил вам в вопросе', false);
        $this->get(route('cabinet.notifications.open', ['id' => $id]))->assertRedirect('/questions/test#r-1');
        $this->assertSame(0, $me->unreadNotifications()->count());
    }

    public function test_someone_elses_notification_cannot_be_opened(): void
    {
        $id = $this->notify($owner = User::factory()->create());

        $this->actingAs(User::factory()->create())->get(route('cabinet.notifications.open', ['id' => $id]))->assertNotFound();
        $this->assertSame(1, $owner->unreadNotifications()->count());
    }

    public function test_read_all_and_the_new_filter(): void
    {
        $me = User::factory()->create();
        $this->notify($me);
        $this->notify($me);
        $this->actingAs($me);

        $this->get(route('cabinet.notifications', ['show' => 'new']))->assertOk()->assertSee('Новые · 2');
        $this->post(route('cabinet.notifications.read-all'))->assertRedirect();
        $this->assertSame(0, $me->unreadNotifications()->count());
        $this->get(route('cabinet.notifications', ['show' => 'new']))->assertSee('Новых уведомлений нет');
    }

    public function test_reaction_notifications_of_the_same_emoji_are_grouped(): void
    {
        $me = User::factory()->create();
        foreach (['Анна', 'Борис'] as $name) {
            $this->notify($me, ['kind' => 'reaction', 'emoji' => '🔥', 'actor_name' => $name, 'target_id' => 'c-5']);
        }

        $this->actingAs($me)->get(route('cabinet.notifications'))->assertSee('и ещё 1 человек', false);
    }

    public function test_profile_saves_with_all_optional_fields_empty_and_keeps_the_name(): void
    {
        $me = User::factory()->create(['name' => 'Прежнее имя']);

        $this->actingAs($me)->put(route('profile.update'), ['name' => '', 'phone' => '', 'location' => '', 'bio' => ''])->assertSessionHasNoErrors();

        $this->assertSame('Прежнее имя', $me->refresh()->name);
    }

    public function test_profile_saves_the_fields_and_validates_the_phone(): void
    {
        $me = User::factory()->create();
        $this->actingAs($me);

        $this->put(route('profile.update'), ['phone' => '123'])->assertSessionHasErrors('phone');
        $this->put(route('profile.update'), ['name' => 'Новое имя', 'phone' => '+7 (900) 111-22-33', 'location' => 'Уфа', 'bio' => 'Чиню технику'])->assertSessionHasNoErrors();

        $me->refresh();
        $this->assertSame(['Новое имя', '+7 (900) 111-22-33', 'Уфа'], [$me->name, $me->phone, $me->location]);
    }

    public function test_password_change_needs_the_current_password(): void
    {
        $me = User::factory()->create(['password' => Hash::make('Old-pass-12345')]);
        $this->actingAs($me);
        $new = ['password' => 'New-pass-67890', 'password_confirmation' => 'New-pass-67890'];

        $this->put(route('profile.update'), $new + ['current_password' => 'wrong'])->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('Old-pass-12345', $me->refresh()->password));

        $this->put(route('profile.update'), ['password' => 'New-pass-67890', 'password_confirmation' => 'other'] + ['current_password' => 'Old-pass-12345'])->assertSessionHasErrors('password');

        $this->put(route('profile.update'), $new + ['current_password' => 'Old-pass-12345'])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('New-pass-67890', $me->refresh()->password));
    }
}
