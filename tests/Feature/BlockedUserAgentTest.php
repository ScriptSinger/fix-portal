<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlockedUserAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BlockedUserAgentTest extends TestCase
{
    use RefreshDatabase;

    public function test_blocked_user_agent_is_rejected_on_public_routes(): void
    {
        BlockedUserAgent::create(['user_agent' => 'msh-pdfmin/1']);

        $this->get('/firmwares', [
            'User-Agent' => 'Mozilla/5.0 msh-pdfmin/1 crawler',
        ])->assertForbidden();
    }

    public function test_blocked_user_agent_is_rejected_on_moonshine_routes(): void
    {
        BlockedUserAgent::create(['user_agent' => 'msh-pdfmin/1']);

        $this->get('/admin/login', [
            'User-Agent' => 'MSH-PDFMIN/1',
        ])->assertForbidden();
    }
}
