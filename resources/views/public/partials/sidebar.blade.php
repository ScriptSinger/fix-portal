@php($variant = $variant ?? 'full')
{{-- Правая колонка: сервис, реклама, Telegram. «Свежие вопросы», «Популярные статьи» и «Популярные категории» здесь не показываем --}}
@if ($variant !== 'tags')
    @include('public.partials.widgets.repair')
@endif
@include('public.partials.widgets.ad')
@if (request()->routeIs('home', 'articles.*', 'questions.*', 'firmwares.*', 'categories.show', 'tags.show'))
    @include('public.partials.widgets.latest-comments')
@endif
@if ($variant !== 'tags' && $variant !== 'catalog')
    @include('public.partials.widgets.telegram')
@endif
