<?php

namespace Tests\Feature;

use App\MoonShine\Resources\TelegramCommunityQuestionResource;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use MoonShine\Laravel\Models\MoonshineUser;
use MoonShine\Laravel\Models\MoonshineUserRole;
use Tests\TestCase;

class TelegramCommunityQuestionAdminTest extends TestCase
{
    use DatabaseTransactions;

    public function test_question_detail_displays_album_photos_without_publication(): void
    {
        $role = MoonshineUserRole::query()->first() ?? MoonshineUserRole::forceCreate(['name' => 'Admin']);
        $admin = MoonshineUser::forceCreate(['moonshine_user_role_id' => $role->id, 'name' => 'Тест', 'email' => uniqid('admin').'@example.invalid', 'password' => bcrypt('secret-pass')]);
        $id = DB::table('telegram_question_messages')->insertGetId([
            'telegram_chat_id' => -100123, 'telegram_message_id' => 987654, 'telegram_update_id' => 987654,
            'telegram_user_id' => 778, 'text' => 'Холодильник не охлаждает', 'registered_at' => now(),
            'payload' => json_encode(['message' => ['media_group_id' => 'album-admin', 'chat' => ['id' => -100123], 'photo' => [['file_id' => 'one']], 'album_photos' => [
                [['file_id' => 'one']], [['file_id' => 'two']], [['file_id' => 'three']],
            ]]]),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('telegram_webhook_updates')->insert([
            'update_id' => 987655,
            'payload' => json_encode(['message' => ['chat' => ['id' => -100123], 'media_group_id' => 'album-admin', 'photo' => [['file_id' => 'four']]]]),
            'received_at' => now(),
        ]);
        $page = $this->actingAs($admin, 'moonshine')
            ->get(app(TelegramCommunityQuestionResource::class)->getDetailPageUrl($id))
            ->assertOk()->assertSee('Фотографии');
        foreach (range(0, 3) as $index) {
            $page->assertSee(route('moonshine.telegram-question-photo', [$id, $index]), false);
        }
        $this->assertDatabaseMissing('telegram_question_publications', ['telegram_question_message_id' => $id]);
    }
}
