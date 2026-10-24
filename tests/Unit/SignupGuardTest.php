<?php

namespace Tests\Unit;

use App\Rules\NotDisposableEmail;
use App\Support\SignupGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SignupGuardTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function request(array $input): Request
    {
        return Request::create('/register', 'POST', $input);
    }

    public function test_human_submission_passes_after_a_pause(): void
    {
        $guard = new SignupGuard();
        Carbon::setTestNow('2026-10-25 12:00:00');
        $token = $guard->token();

        Carbon::setTestNow('2026-10-25 12:00:10');

        $this->assertNull($guard->reason($this->request(['form_token' => $token])));
    }

    public function test_instant_submission_is_rejected(): void
    {
        $guard = new SignupGuard();
        Carbon::setTestNow('2026-10-25 12:00:00');
        $token = $guard->token();

        $this->assertSame('too_fast', $guard->reason($this->request(['form_token' => $token])));
    }

    public function test_stale_token_is_rejected(): void
    {
        $guard = new SignupGuard();
        Carbon::setTestNow('2026-10-25 12:00:00');
        $token = $guard->token();

        Carbon::setTestNow('2026-10-25 15:00:00');

        $this->assertSame('expired', $guard->reason($this->request(['form_token' => $token])));
    }

    public function test_filled_honeypot_missing_and_forged_tokens_are_rejected(): void
    {
        $guard = new SignupGuard();
        Carbon::setTestNow('2026-10-25 12:00:00');
        $token = $guard->token();
        Carbon::setTestNow('2026-10-25 12:00:10');

        $this->assertSame('honeypot', $guard->reason($this->request(['form_token' => $token, 'website' => 'x'])));
        $this->assertSame('no_token', $guard->reason($this->request([])));
        $this->assertSame('bad_token', $guard->reason($this->request(['form_token' => 'forged'])));
    }

    public function test_disposable_email_domains_are_rejected(): void
    {
        $rule = ['email' => [new NotDisposableEmail()]];

        $this->assertTrue(Validator::make(['email' => 'user@mailinator.com'], $rule)->fails());
        $this->assertTrue(Validator::make(['email' => 'user@sub.yopmail.com'], $rule)->fails());
        $this->assertFalse(Validator::make(['email' => 'user@gmail.com'], $rule)->fails());
        $this->assertFalse(Validator::make(['email' => 'user@mail.ru'], $rule)->fails());
    }
}
