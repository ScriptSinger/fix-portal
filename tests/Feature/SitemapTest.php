<?php

namespace Tests\Feature;

use App\Models\Firmware;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Tests\Concerns\CreatesDiscussions;
use Tests\TestCase;

/** Карта сайта: нужные страницы на месте, даты изменения честные, файл пишется целиком. Команда пишет во временный файл, а не в public/sitemap.xml. */
class SitemapTest extends TestCase
{
    use CreatesDiscussions, DatabaseTransactions;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->path = tempnam(sys_get_temp_dir(), 'sitemap') . '.xml';
    }

    protected function tearDown(): void
    {
        @unlink($this->path);
        @unlink($this->path . '.tmp');
        parent::tearDown();
    }

    private function generate(): \SimpleXMLElement
    {
        Artisan::call('sitemap:generate', ['--path' => $this->path]);

        $xml = simplexml_load_file($this->path);
        $this->assertNotFalse($xml, 'карта сайта должна быть корректным XML');

        return $xml;
    }

    private function entry(\SimpleXMLElement $xml, string $loc): ?\SimpleXMLElement
    {
        foreach ($xml->url as $url) {
            if ((string) $url->loc === $loc) {
                return $url;
            }
        }

        return null;
    }

    public function test_the_static_text_pages_are_listed_without_a_made_up_date(): void
    {
        $xml = $this->generate();

        foreach ([route('about'), route('how-it-works')] as $loc) {
            $entry = $this->entry($xml, $loc);
            $this->assertNotNull($entry, $loc . ' должна быть в карте сайта');
            $this->assertCount(0, $entry->lastmod, 'у текстовых страниц нет настоящей даты изменения, значит и lastmod нет');
        }
    }

    public function test_list_pages_take_the_date_of_their_latest_content_not_the_generation_time(): void
    {
        $question = $this->makeQuestion(User::factory()->create());
        $question->forceFill(['updated_at' => '2031-05-06 07:08:09'])->saveQuietly();

        $entry = $this->entry($this->generate(), route('questions.index'));

        $this->assertStringStartsWith('2031-05-06', (string) $entry->lastmod);
    }

    public function test_the_file_is_written_atomically_and_no_temporary_file_is_left(): void
    {
        $this->generate();

        $this->assertFileExists($this->path);
        $this->assertFileDoesNotExist($this->path . '.tmp');
    }

    public function test_thin_firmwares_are_not_listed_but_informative_ones_are(): void
    {
        $thin = Firmware::forceCreate(['path_id' => random_int(1_000_000, 9_999_999), 'title' => 'THIN_' . uniqid(), 'extension' => '.bin', 'size' => 100]);
        $good = Firmware::forceCreate([
            'path_id' => random_int(1_000_000, 9_999_999), 'title' => 'GOOD_' . uniqid(), 'extension' => '.bin', 'size' => 100,
            'data' => '<table><tr><td>Модель</td><td>WISL105CSI</td></tr></table>',
        ]);

        $xml = $this->generate();

        $this->assertNull($this->entry($xml, route('firmwares.show', ['firmware' => $thin->id])));
        $this->assertNotNull($this->entry($xml, route('firmwares.show', ['firmware' => $good->id])));
    }

    public function test_robots_txt_points_to_the_sitemap(): void
    {
        $this->assertStringContainsString('Sitemap: https://ufamasters.ru/sitemap.xml', file_get_contents(public_path('robots.txt')));
    }
}
