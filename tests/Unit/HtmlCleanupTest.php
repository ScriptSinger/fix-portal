<?php

namespace Tests\Unit;

use App\Support\HtmlCleanup;
use PHPUnit\Framework\TestCase;

class HtmlCleanupTest extends TestCase
{
    public function test_inline_styles_are_removed_but_the_structure_stays(): void
    {
        $html = '<p style="margin: 0"><span style="font-size: 18px; font-family: Söhne">Текст <b style=\'color:red\'>важно</b></span></p><a href="/x" style="color:blue">ссылка</a>';

        $this->assertSame('<p><span>Текст <b>важно</b></span></p><a href="/x">ссылка</a>', HtmlCleanup::stripInlineStyles($html));
    }

    public function test_font_tag_presentation_attributes_are_removed(): void
    {
        $this->assertSame('<font>слово</font>', HtmlCleanup::stripInlineStyles('<font face="Arial" size="5" color="#f00">слово</font>'));
    }

    public function test_plain_text_and_other_attributes_are_untouched(): void
    {
        $this->assertSame('Просто текст', HtmlCleanup::stripInlineStyles('Просто текст'));
        $this->assertSame('<a href="/a" class="x" title="style">a</a>', HtmlCleanup::stripInlineStyles('<a href="/a" class="x" title="style">a</a>'));
    }
}
