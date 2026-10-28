<?php

namespace Tests\Unit;

use App\Support\Linkify;
use PHPUnit\Framework\TestCase;

class LinkifyTest extends TestCase
{
    private const REL = 'target="_blank" rel="nofollow noopener noreferrer ugc"';

    public function test_plain_url_becomes_a_link(): void
    {
        $this->assertSame(
            'Смотрите <a href="https://ufamasters.ru/firmwares?search=wiun100.s19" ' . self::REL . '>https://ufamasters.ru/firmwares?search=wiun100.s19</a>',
            Linkify::html('Смотрите https://ufamasters.ru/firmwares?search=wiun100.s19')
        );
    }

    public function test_punctuation_after_the_url_stays_outside_the_link(): void
    {
        $this->assertSame(
            '(<a href="https://a.ru/x" ' . self::REL . '>https://a.ru/x</a>), и ещё <a href="https://b.ru" ' . self::REL . '>https://b.ru</a>.',
            Linkify::html('(https://a.ru/x), и ещё https://b.ru.')
        );
    }

    public function test_escaped_ampersand_and_quotes_are_handled(): void
    {
        $out = Linkify::html('https://a.ru/?x=1&amp;y=2 и &quot;https://b.ru&quot;');

        $this->assertStringContainsString('href="https://a.ru/?x=1&amp;y=2"', $out);
        $this->assertStringContainsString('&quot;<a href="https://b.ru"', $out);
        $this->assertStringContainsString('</a>&quot;', $out);
    }

    public function test_urls_in_tags_and_existing_links_are_left_alone(): void
    {
        $html = '<img src="https://a.ru/p.jpg" class="img-fluid"> <a href="https://b.ru">https://b.ru</a>';

        $this->assertSame($html, Linkify::html($html));
    }

    public function test_only_http_and_https_and_nothing_else(): void
    {
        $this->assertSame('javascript:alert(1) ftp://a.ru', Linkify::html('javascript:alert(1) ftp://a.ru'));
        $this->assertSame('', Linkify::html(null));
    }
}
