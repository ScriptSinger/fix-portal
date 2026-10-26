@props(['active' => null])
{{-- Разделы статей: категории (/categories/...) --}}
<x-ui.side-nav heading="Разделы" label="Категории статей" :items="collect([['title' => 'Все статьи', 'url' => route('articles.index'), 'active' => $active === null]])
    ->concat($popular_categories->map(fn ($c) => ['title' => $c->title, 'url' => route('categories.show', ['category' => $c->slug]), 'count' => $c->posts_count, 'active' => $active === $c->slug]))" />
