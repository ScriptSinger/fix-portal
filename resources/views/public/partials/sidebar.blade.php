@php($variant = $variant ?? 'full')
{{-- Правая колонка: сервис, реклама, Telegram. «Свежие вопросы», «Популярные статьи» и «Популярные категории» здесь не показываем --}}
@if ($variant !== 'tags')
    @include('public.partials.widgets.repair')
@endif
@include('public.partials.widgets.ad')
@if ($variant !== 'tags' && $variant !== 'catalog')
    @include('public.partials.widgets.telegram')
@endif
