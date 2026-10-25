@php($variant = $variant ?? 'full')
{{-- Правая колонка: сервис, реклама, Telegram. «Свежие вопросы» и «Популярные статьи» здесь не показываем --}}
@if ($variant !== 'tags')
    @include('public.partials.widgets.repair')
@endif
@include('public.partials.widgets.ad')
@if ($variant !== 'tags' && $variant !== 'catalog')
    @include('public.partials.widgets.telegram')
@endif
@unless (View::hasSection('sidebar-left'))
    @include('public.partials.widgets.categories')
@endunless
