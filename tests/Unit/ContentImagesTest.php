<?php

namespace Tests\Unit;

use App\Support\ContentImages;
use PHPUnit\Framework\TestCase;

class ContentImagesTest extends TestCase
{
    public function test_adds_alt_to_images_without_it(): void
    {
        $html = '<p>Текст</p><img src="/a.png" class="img-fluid"><img src="/b.png">';

        $result = ContentImages::withAlt($html, 'Ремонт "Bosch"');

        $this->assertStringContainsString('<img alt="Ремонт &quot;Bosch&quot; — иллюстрация 1" src="/a.png" class="img-fluid">', $result);
        $this->assertStringContainsString('иллюстрация 2" src="/b.png"', $result);
    }

    public function test_keeps_existing_alt_even_when_empty(): void
    {
        $html = '<img src="/a.png" alt=""><img alt="Схема" src="/b.png">';

        $this->assertSame($html, ContentImages::withAlt($html, 'Статья'));
    }

    public function test_handles_empty_content(): void
    {
        $this->assertSame('', ContentImages::withAlt(null, 'Статья'));
    }
}
