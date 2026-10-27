<?php

namespace Tests\Feature;

use App\Models\Appliance;
use App\Models\Image;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\CreatesDiscussions;
use Tests\TestCase;

/** Форма «Задать вопрос» и правка: проверка полей, безопасное сохранение текста и фото, права. */
class QuestionFormTest extends TestCase
{
    use CreatesDiscussions, DatabaseTransactions;

    private function valid(array $over = []): array
    {
        return $over + [
            'title' => 'Стиральная машина не сливает воду ' . uniqid(),
            'appliance_id' => Appliance::factory()->create()->id,
            'model' => 'Beko WKL 13540 K',
            'error_code' => 'E03',
            'description' => 'Вода остаётся в баке, насос гудит. Фильтр чистил, шланг прочистил, ничего не помогло.',
        ];
    }

    private function image(User $owner, string $url = '/storage/images/users/user_1/a.jpg'): Image
    {
        $image = new Image();
        $image->url = $url;
        $image->mime = 'image/jpeg';
        $image->size = 10;
        $image->user()->associate($owner);
        $image->save();

        return $image;
    }

    public function test_title_of_a_deleted_question_can_be_asked_again(): void
    {
        $user = User::factory()->create();
        $data = $this->valid();
        $this->makeQuestion($user, ['title' => $data['title']])->delete();

        $this->actingAs($user)->post(route('questions.store'), $data)->assertRedirect();

        $again = Question::where('title', $data['title'])->firstOrFail();
        $this->get(route('questions.show', ['question' => $again->slug]))->assertOk();
    }

    public function test_guests_cannot_ask(): void
    {
        $this->post(route('questions.store'), $this->valid())->assertRedirect(route('login'));
    }

    public function test_valid_question_is_saved_with_escaped_text(): void
    {
        $user = User::factory()->create();
        $data = $this->valid(['description' => 'Не греет вода <b>совсем</b>, ТЭН проверил, он целый, плату не смотрел.']);

        $this->actingAs($user)->post(route('questions.store'), $data);

        $question = Question::where('title', $data['title'])->firstOrFail();
        $this->get(route('questions.show', ['question' => $question->slug]))->assertOk();
        $this->assertSame($user->id, $question->user_id);
        $this->assertStringContainsString('&lt;b&gt;совсем&lt;/b&gt;', $question->description);
        $this->assertSame('E03', $question->error_code);
    }

    public function test_after_publishing_the_user_lands_on_the_new_question(): void
    {
        $data = $this->valid();

        $response = $this->actingAs(User::factory()->create())->post(route('questions.store'), $data);

        $question = Question::where('title', $data['title'])->firstOrFail();
        $response->assertRedirect(route('questions.show', ['question' => $question->slug]))->assertSessionHas('success', 'Вопрос опубликован');
    }

    public function test_title_description_and_appliance_are_validated(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('questions.store'), $this->valid(['title' => 'коротко']))->assertSessionHasErrors('title');
        $this->post(route('questions.store'), $this->valid(['title' => str_repeat('а', 151)]))->assertSessionHasErrors('title');
        $this->post(route('questions.store'), $this->valid(['description' => 'мало']))->assertSessionHasErrors('description');
        $this->post(route('questions.store'), $this->valid(['appliance_id' => 999999]))->assertSessionHasErrors('appliance_id');
    }

    public function test_duplicate_title_is_rejected(): void
    {
        $user = User::factory()->create();
        $existing = $this->makeQuestion($user);

        $this->actingAs($user)->post(route('questions.store'), $this->valid(['title' => $existing->title]))
            ->assertSessionHasErrors(['title' => 'Вопрос с таким заголовком уже есть. Уточните его (модель, код ошибки) или найдите существующий вопрос.']);
    }

    public function test_photo_alone_is_enough_but_only_own_photos_are_attached(): void
    {
        $user = User::factory()->create();
        $mine = $this->image($user, '/storage/images/users/user_1/mine.jpg');
        $foreign = $this->image(User::factory()->create(), '/storage/images/users/user_2/foreign.jpg');
        $data = $this->valid(['description' => '', 'images' => [$mine->url, $foreign->url, '/storage/evil.jpg']]);

        $this->actingAs($user)->post(route('questions.store'), $data)->assertRedirect();

        $description = Question::where('title', $data['title'])->value('description');
        $this->assertStringContainsString($mine->url, $description);
        $this->assertStringNotContainsString('foreign.jpg', $description);
        $this->assertStringNotContainsString('evil.jpg', $description);
    }

    public function test_only_the_owner_can_edit_and_the_old_title_is_not_a_duplicate_of_itself(): void
    {
        $owner = User::factory()->create();
        $question = $this->makeQuestion($owner);
        $url = route('questions.update', ['question' => $question->slug]);
        $payload = $this->valid(['title' => $question->title, 'appliance_id' => $question->appliance_id]);

        $this->actingAs(User::factory()->create())->put($url, $payload)->assertForbidden();

        $this->actingAs($owner)->put($url, array_merge($payload, ['description' => 'Дополнил: после замены насоса ошибка пропала, но вода ещё остаётся.']))
            ->assertSessionHasNoErrors()->assertRedirect(route('questions.show', ['question' => $question->slug]));
        $this->assertStringContainsString('после замены насоса', $question->refresh()->description);
    }

    public function test_edit_keeps_the_photos_that_were_already_in_the_question(): void
    {
        $owner = User::factory()->create();
        $question = $this->makeQuestion($owner, ['description' => 'Старый текст вопроса про насос и шланг.<img src="/storage/images/users/user_9/old.jpg" class="img-fluid">']);

        $this->actingAs($owner)->put(route('questions.update', ['question' => $question->slug]), [
            'title' => $question->title, 'appliance_id' => $question->appliance_id,
            'description' => 'Новый текст вопроса про насос и шланг, подробнее описал проблему.',
            'images' => ['/storage/images/users/user_9/old.jpg'],
        ])->assertSessionHasNoErrors();

        $this->assertStringContainsString('/storage/images/users/user_9/old.jpg', $question->refresh()->description);
    }
}
