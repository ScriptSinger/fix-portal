<?php

namespace Tests\Feature;

use App\Models\SocialAccount;
use App\Models\User;
use App\Services\SocialLogin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Two\User as SocialUser;
use Tests\TestCase;

class SocialLoginTest extends TestCase
{
    use DatabaseTransactions;

    private function social(string $id, ?string $email, string $name = 'Иван Иванов'): SocialUser
    {
        return (new SocialUser())->map(['id' => $id, 'name' => $name, 'nickname' => 'ivan', 'email' => $email]);
    }

    public function test_new_user_is_created_verified_and_linked(): void
    {
        $user = app(SocialLogin::class)->resolve('yandex', $this->social('y-1', 'New.User@Yandex.ru'));

        $this->assertNotNull($user);
        $this->assertSame('new.user@yandex.ru', $user->email);
        $this->assertNotNull($user->email_verified_at);
        $this->assertDatabaseHas('social_accounts', ['user_id' => $user->id, 'provider' => 'yandex', 'provider_user_id' => 'y-1']);
    }

    public function test_second_login_finds_the_same_user_by_link_even_if_the_email_changed(): void
    {
        $first = app(SocialLogin::class)->resolve('yandex', $this->social('y-2', 'one@yandex.ru'));
        $second = app(SocialLogin::class)->resolve('yandex', $this->social('y-2', 'other@yandex.ru'));

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, SocialAccount::where('provider_user_id', 'y-2')->count());
    }

    public function test_existing_verified_user_with_the_same_email_gets_the_link_and_keeps_the_password(): void
    {
        $existing = User::factory()->create(['email' => 'master@mail.ru', 'password' => Hash::make('secret-pass')]);

        $user = app(SocialLogin::class)->resolve('vkid', $this->social('v-1', 'master@mail.ru'));

        $this->assertSame($existing->id, $user->id);
        $this->assertTrue(Hash::check('secret-pass', $user->fresh()->password));
    }

    public function test_unverified_account_on_the_same_email_is_verified_and_its_password_is_reset(): void
    {
        // Так выглядит попытка занять чужую почту: аккаунт создан по паролю и не подтверждён
        $squatter = User::factory()->unverified()->create(['email' => 'victim@mail.ru', 'password' => Hash::make('attacker-pass')]);

        $user = app(SocialLogin::class)->resolve('yandex', $this->social('y-3', 'victim@mail.ru'));

        $this->assertSame($squatter->id, $user->id);
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertFalse(Hash::check('attacker-pass', $user->fresh()->password));
    }

    public function test_no_email_means_no_account(): void
    {
        $this->assertNull(app(SocialLogin::class)->resolve('vkid', $this->social('v-2', null)));
        $this->assertDatabaseMissing('social_accounts', ['provider_user_id' => 'v-2']);
    }

    public function test_deleted_user_can_sign_in_again_as_a_new_account(): void
    {
        $gone = User::factory()->create(['email' => 'gone@mail.ru']);
        app(SocialLogin::class)->resolve('yandex', $this->social('y-4', 'gone@mail.ru'));
        $gone->delete();

        $again = app(SocialLogin::class)->resolve('yandex', $this->social('y-4', 'gone@mail.ru'));

        $this->assertNotNull($again);
        $this->assertNotSame($gone->id, $again->id);
        $this->assertSame(1, User::where('email', 'gone@mail.ru')->count());
        $this->assertDatabaseMissing('social_accounts', ['user_id' => $gone->id]);
    }

    public function test_long_names_are_cut_to_the_registration_limit(): void
    {
        $user = app(SocialLogin::class)->resolve('yandex', $this->social('y-5', 'long@yandex.ru', str_repeat('Я', 80)));

        $this->assertSame(30, mb_strlen($user->name));
    }

    public function test_html_entities_in_the_provider_name_are_decoded(): void
    {
        $user = app(SocialLogin::class)->resolve('vkid', $this->social('v-3', 'almir@mail.ru', 'Al&#39;mir Nurislamov'));

        $this->assertSame("Al'mir Nurislamov", $user->name);
    }
}
