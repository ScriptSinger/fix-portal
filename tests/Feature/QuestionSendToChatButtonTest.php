<?php

namespace Tests\Feature;

use App\Jobs\SendQuestionAnnouncement;
use App\Models\QuestionAnnouncement;
use App\Models\User;
use App\MoonShine\Resources\QuestionResource;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use MoonShine\Laravel\Models\MoonshineUser;
use MoonShine\Laravel\Models\MoonshineUserRole;
use Tests\Concerns\CreatesDiscussions;
use Tests\TestCase;

/** Кнопка «Отправить в чат» в списке вопросов: выборочно поднимает старый вопрос, дублей не плодит. */
class QuestionSendToChatButtonTest extends TestCase
{
    use CreatesDiscussions, DatabaseTransactions;

    private ?MoonshineUser $admin = null;

    private function admin(): MoonshineUser
    {
        if ($this->admin) {
            return $this->admin;
        }

        $role = MoonshineUserRole::query()->first() ?? MoonshineUserRole::forceCreate(['name' => 'Admin']);

        return $this->admin = MoonshineUser::forceCreate([
            'moonshine_user_role_id' => $role->id,
            'name' => 'Тест',
            'email' => uniqid('admin') . '@example.invalid',
            'password' => bcrypt('secret-pass'),
        ]);
    }

    private function telegram(): void
    {
        config(['services.telegram' => [
            'bot_token' => '123456:SECRET-TOKEN',
            'chat_id' => '-5494489427',
            'proxy' => null,
            'api_url' => 'https://api.telegram.org',
        ]]);
    }

    private function press(int $questionId): void
    {
        $resource = app(QuestionResource::class);
        $url = $resource->getAsyncMethodUrl('announce', params: ['resourceItem' => $questionId], page: $resource->getIndexPage());

        $this->actingAs($this->admin(), 'moonshine')->get($url)->assertOk();
    }

    public function test_list_shows_the_button_for_a_question_that_was_not_sent(): void
    {
        $question = $this->makeQuestion(User::factory()->create());

        $this->actingAs($this->admin(), 'moonshine')
            ->get(app(QuestionResource::class)->getIndexPageUrl())
            ->assertOk()
            ->assertSee($question->title)
            ->assertSee('Отправить в чат');
    }

    public function test_button_queues_one_announcement(): void
    {
        $this->telegram();
        Queue::fake();
        $question = $this->makeQuestion(User::factory()->create());

        $this->press($question->id);

        $this->assertSame(1, QuestionAnnouncement::where('question_id', $question->id)->count());
        Queue::assertPushed(SendQuestionAnnouncement::class, 1);
    }

    public function test_pressing_twice_does_not_duplicate(): void
    {
        $this->telegram();
        Queue::fake();
        $question = $this->makeQuestion(User::factory()->create());

        $this->press($question->id);
        $this->press($question->id);

        $this->assertSame(1, QuestionAnnouncement::where('question_id', $question->id)->count());
        Queue::assertPushed(SendQuestionAnnouncement::class, 1);
    }

    public function test_button_is_hidden_once_the_question_was_announced(): void
    {
        $this->telegram();
        Queue::fake();
        $question = $this->makeQuestion(User::factory()->create());
        $this->press($question->id);

        $this->actingAs($this->admin(), 'moonshine')
            ->get(app(QuestionResource::class)->getIndexPageUrl())
            ->assertOk()
            ->assertSee($question->title)
            ->assertDontSee('Отправить в чат');
    }

    public function test_without_configured_channels_nothing_is_queued(): void
    {
        Queue::fake();
        $question = $this->makeQuestion(User::factory()->create());

        $this->press($question->id);

        $this->assertSame(0, QuestionAnnouncement::where('question_id', $question->id)->count());
        Queue::assertNothingPushed();
    }
}
