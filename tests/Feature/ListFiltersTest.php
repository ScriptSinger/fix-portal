<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\CreatesDiscussions;
use Tests\TestCase;

/** Фильтры списков через пайплайн: вопросы, страницы техники и статьи. */
class ListFiltersTest extends TestCase
{
    use CreatesDiscussions, DatabaseTransactions;

    public function test_question_text_filter_finds_by_title_and_treats_wildcards_literally(): void
    {
        $user = User::factory()->create();
        $a = $this->makeQuestion($user, ['title' => 'Фильтрация сушилки ' . uniqid()]);
        $b = $this->makeQuestion($user, ['title' => 'Другой вопрос про насос ' . uniqid()]);

        $this->get(route('questions.index', ['text' => 'Фильтрация сушилки']))->assertSee($a->title)->assertDontSee($b->title);
        $this->get(route('questions.index', ['text' => '%']))->assertDontSee($a->title)->assertDontSee($b->title);
    }

    public function test_status_filter_on_an_appliance_page(): void
    {
        $user = User::factory()->create();
        $solved = $this->makeQuestion($user, ['title' => 'Решённый про технику ' . uniqid()]);
        $solved->forceFill(['solved_at' => now()])->save();
        $open = $this->makeQuestion($user, ['title' => 'Открытый про технику ' . uniqid(), 'appliance_id' => $solved->appliance_id]);
        $elsewhere = $this->makeQuestion($user, ['title' => 'Чужая техника ' . uniqid()]);
        $url = route('public.appliances.show', ['appliance' => $solved->appliance->slug]);

        $this->get($url . '?status=solved')->assertSee($solved->title)->assertDontSee($open->title);
        $this->get($url . '?status=open')->assertSee($open->title)->assertDontSee($solved->title);
        $this->get($url)->assertSee($solved->title)->assertSee($open->title)->assertDontSee($elsewhere->title);
    }

    public function test_status_and_text_filters_combine(): void
    {
        $user = User::factory()->create();
        $match = $this->makeQuestion($user, ['title' => 'Сочетание фильтров раз ' . uniqid()]);
        $match->forceFill(['solved_at' => now()])->save();
        $unsolved = $this->makeQuestion($user, ['title' => 'Сочетание фильтров два ' . uniqid()]);

        $this->get(route('questions.index', ['text' => 'Сочетание фильтров', 'status' => 'solved']))
            ->assertSee($match->title)->assertDontSee($unsolved->title);
    }

    public function test_article_text_filter_finds_by_title_and_ignores_wildcards(): void
    {
        $post = $this->subject('post');

        // заголовок статьи есть и в подвале (последние статьи), поэтому смотрим на саму выборку списка
        $this->get(route('articles.index', ['text' => $post->title]))->assertOk()->assertViewHas('posts', fn ($posts) => $posts->pluck('id')->contains($post->id));
        $this->get(route('articles.index', ['text' => '%']))->assertOk()->assertViewHas('posts', fn ($posts) => $posts->total() === 0);
        $this->assertInstanceOf(Post::class, $post);
    }
}
