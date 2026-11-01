<?php

namespace Tests\Feature;

use App\Models\SiteWidget;
use Database\Seeders\SiteWidgetSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class WidgetSettingsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SiteWidgetSeeder::class);
    }

    public function test_sidebar_widgets_are_enabled_by_default(): void
    {
        Cache::forget('public_composer_data');

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('РемБытТехника')
            ->assertSee('Telegram-чат сообщества');
    }

    public function test_sidebar_widgets_can_be_disabled_from_widget_resources(): void
    {
        SiteWidget::query()->whereIn('key', ['repair', 'telegram'])->update(['enabled' => false]);
        Cache::forget('public_composer_data');

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertDontSee('РемБытТехника')
            ->assertDontSee('Telegram-чат сообщества');
    }
}
