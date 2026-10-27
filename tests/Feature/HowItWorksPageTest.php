<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Reputation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\CreatesDiscussions;
use Tests\TestCase;

/** Страница «Как это работает»: шаги вопроса, звания с порогами из настроек репутации, ссылки на неё. */
class HowItWorksPageTest extends TestCase
{
    use CreatesDiscussions, DatabaseTransactions;

    public function test_the_page_opens_and_explains_the_steps_and_the_ranks(): void
    {
        $response = $this->get(route('how-it-works'))->assertOk()
            ->assertSee('Опишите проблему')->assertSee('Вам отвечают')->assertSee('Вы отмечаете правильный ответ')->assertSee('нажмите «Это решение»')->assertSee('Разбор остаётся на сайте')
            ->assertSee('Звания и репутация')->assertSee('До первого решения звания нет.');

        // звания и пороги берутся из тех же настроек, что и расчёт репутации
        foreach (Reputation::RANKS as $threshold => $title) {
            $response->assertSee($title)->assertSee('от ' . $threshold . ' ');
        }
    }

    public function test_the_helpers_list_lives_on_about_and_the_rank_page_links_to_it(): void
    {
        $author = User::factory()->create();
        $helper = User::factory()->create(['name' => 'Мастер Тестов']);
        $question = $this->makeQuestion($author);
        $answer = $this->commentOn($question, $helper);
        $question->forceFill(['solved_at' => now(), 'solution_type' => 'comment', 'solution_id' => $answer->id])->save();

        $this->get(route('about'))->assertOk()->assertSee('id="helpers"', false)->assertSee('Кто помогает в этом месяце')->assertSee('Мастер Тестов');
        $this->get(route('how-it-works'))->assertOk()->assertDontSee('Мастер Тестов')
            ->assertSee('href="' . route('about') . '#helpers"', false)->assertSee('Кто помогает в этом месяце →');
    }

    public function test_the_helpers_block_is_hidden_when_nobody_has_solutions_yet(): void
    {
        $this->get(route('about'))->assertOk()->assertDontSee('id="helpers"', false);
    }

    public function test_about_and_the_questions_sidebar_link_to_the_page_but_the_footer_does_not(): void
    {
        $url = route('how-it-works');

        $this->get(route('about'))->assertOk()->assertSee('href="' . $url . '"', false)->assertSee('Знания собраны в трёх местах')->assertSee('Вопросы работают так')->assertSee('Подробнее →');
        // ссылка в колонке «Техника» на странице вопросов (карточка для компьютера и пункт в панели телефона)
        $this->get(route('questions.index'))->assertOk()->assertSee('href="' . $url . '"', false)->assertSee('Как это работает');
        // в подвале страниц «О нас» и «Как это работает» нет: там их не замечают
        $this->get(route('articles.index'))->assertOk()->assertDontSee('href="' . $url . '"', false);
    }

    public function test_the_ask_form_and_the_cabinet_link_to_it(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('questions.index'))->assertOk()->assertSee('Как задать хороший вопрос');
        $this->get(route('cabinet.index'))->assertOk()->assertSee('Что это значит?')->assertSee(route('how-it-works') . '#ranks', false);
    }
}
