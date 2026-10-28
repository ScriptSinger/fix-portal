<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\CreatesDiscussions;
use Tests\TestCase;

/** Картинка для превью ссылок: лёгкая, нужного размера, подключена по умолчанию. */
class OgImageTest extends TestCase
{
    use CreatesDiscussions, DatabaseTransactions;

    public function test_default_og_image_is_light_and_has_the_standard_size(): void
    {
        $path = public_path('assets/front/images/og-default.jpg');

        $this->assertFileExists($path);
        [$width, $height, $type] = getimagesize($path);
        $this->assertSame([1200, 630], [$width, $height]);
        $this->assertSame(IMAGETYPE_JPEG, $type);
        $this->assertLessThan(200 * 1024, filesize($path), 'для карточки хватает 200 КБ, прежний снимок весил 1,1 МБ');
    }

    public function test_pages_use_the_default_image_with_its_size_and_description(): void
    {
        $image = asset('assets/front/images/og-default.jpg');

        $this->get('/about')->assertOk()
            ->assertSee('<meta property="og:image" content="' . $image . '">', false)
            ->assertSee('<meta property="og:image:width" content="1200">', false)
            ->assertSee('<meta property="og:image:height" content="630">', false)
            ->assertSee('<meta name="twitter:image" content="' . $image . '">', false)
            ->assertSee('og:image:alt', false)
            ->assertDontSee('power_unit.jpg', false);
    }

    public function test_question_page_uses_the_default_image(): void
    {
        $question = $this->makeQuestion(User::factory()->create());

        $this->get($this->pageOf($question))->assertOk()
            ->assertSee('og-default.jpg', false)
            ->assertDontSee('<meta property="og:image" content="' . asset('assets/front/images/power_unit.jpg') . '">', false);
    }
}
