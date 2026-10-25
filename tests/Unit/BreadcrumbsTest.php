<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class BreadcrumbsTest extends TestCase
{
    private function ld(string $html): array
    {
        preg_match('~<script type="application/ld\+json">(.*?)</script>~s', $html, $m);

        return json_decode($m[1], true);
    }

    public function test_home_is_prepended_and_last_item_is_the_current_page(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.breadcrumbs :items="[['Вопросы', route('questions.index')], ['Холодильник не морозит', null]]" />
        BLADE);
        $items = $this->ld($html)['itemListElement'];

        $this->assertSame(['Главная', 'Вопросы', 'Холодильник не морозит'], array_column($items, 'name'));
        $this->assertSame([1, 2, 3], array_column($items, 'position'));
        $this->assertSame(route('articles.index'), $items[0]['item']);
        $this->assertSame(route('questions.index'), $items[1]['item']);
        $this->assertSame(url()->current(), $items[2]['item']);
    }

    public function test_old_home_item_is_not_duplicated(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.breadcrumbs :items="[['Статьи', route('articles.index')], ['Категории', null]]" />
        BLADE);

        $this->assertSame(['Главная', 'Категории'], array_column($this->ld($html)['itemListElement'], 'name'));
    }

    public function test_visible_list_matches_the_markup(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.breadcrumbs :items="[['Прошивки', route('firmwares.index')], ['WISL', null]]" />
        BLADE);

        $this->assertSame(substr_count($html, '<li '), count($this->ld($html)['itemListElement']));
        $this->assertStringContainsString('aria-current="page"', $html);
    }
}
