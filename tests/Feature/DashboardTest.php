<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\MoonShine\Resources\UserResource;
use App\MoonShine\Resources\VisitorResource;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use MoonShine\Laravel\Models\MoonshineUser;
use MoonShine\Laravel\Models\MoonshineUserRole;
use Tests\TestCase;

final class DashboardTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get(route('moonshine.index'))
            ->assertRedirect();
    }

    public function test_admin_can_open_dashboard_with_empty_database(): void
    {
        $response = $this->actingAs($this->admin(), 'moonshine')
            ->get(route('moonshine.index'));

        $response
            ->assertOk()
            ->assertSee('Статьи')
            ->assertSee('Вопросы')
            ->assertSee('Прошивки')
            ->assertSee('Пользователи: с подтверждённой почтой из всех')
            ->assertSee('Онлайн сейчас')
            ->assertSee('Заходили сегодня')
            ->assertSee('Обзор')
            ->assertSee('Коммуникации')
            ->assertSee('Публикации и письма')
            ->assertSee('В очереди на публикацию')
            ->assertSee('Отправлено в каналы')
            ->assertSee('Ошибки публикации')
            ->assertSee('Отправленные письма')
            ->assertDontSee('window.location.href');
    }

    public function test_dashboard_does_not_render_inline_navigation_handlers(): void
    {
        $response = $this->actingAs($this->admin(), 'moonshine')
            ->get(route('moonshine.index'));

        $response
            ->assertOk()
            ->assertSee('Открыть подтверждённых пользователей')
            ->assertSee('Открыть пользователей онлайн')
            ->assertSee('Открыть посещения за сегодня')
            ->assertSee(app(UserResource::class)->getIndexPageUrl(['query-tag' => 'verified']))
            ->assertSee(app(VisitorResource::class)->getIndexPageUrl(['query-tag' => VisitorResource::TAG_ONLINE]))
            ->assertSee(app(VisitorResource::class)->getIndexPageUrl(['query-tag' => VisitorResource::TAG_TODAY]))
            ->assertDontSee('onclick=')
            ->assertDontSee('window.location.href');
    }

    private function admin(): MoonshineUser
    {
        $role = MoonshineUserRole::query()->first()
            ?? MoonshineUserRole::forceCreate(['name' => 'Admin']);

        return MoonshineUser::forceCreate([
            'moonshine_user_role_id' => $role->id,
            'name' => 'Тест Dashboard',
            'email' => uniqid('dashboard-admin').'@example.invalid',
            'password' => bcrypt('secret-pass'),
        ]);
    }
}
