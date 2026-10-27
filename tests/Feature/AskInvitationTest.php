<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/** Приглашение «Задать вопрос»: гость видит его так же, как вошедший, и после входа попадает к форме. */
class AskInvitationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_sees_the_same_invitation_as_a_link_to_the_ask_page(): void
    {
        $this->get(route('questions.index'))->assertOk()
            ->assertSee('href="' . route('questions.create') . '"', false)->assertSee('нужен вход')->assertSee('pencil-line', false);
    }

    public function test_the_ask_page_asks_a_guest_to_log_in_and_a_user_goes_to_the_form(): void
    {
        $this->get(route('questions.create'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())->get(route('questions.create'))->assertRedirect(route('questions.index') . '#ask');
    }

    public function test_the_guest_is_remembered_so_that_login_brings_them_back_to_the_ask_page(): void
    {
        // после входа Laravel отправляет на запомненный адрес (AuthenticatedSessionController использует redirect()->intended)
        $this->get(route('questions.create'))->assertRedirect(route('login'))->assertSessionHas('url.intended', route('questions.create'));
    }

    public function test_the_firmware_catalog_has_a_visible_search_that_keeps_the_filters(): void
    {
        $this->get(route('firmwares.index', ['brand' => 'Indesit']))->assertOk()
            ->assertSee('id="f-search-top"', false)->assertSee('name="brand" value="Indesit"', false);
    }
}
