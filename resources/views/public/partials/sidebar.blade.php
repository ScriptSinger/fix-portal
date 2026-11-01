@php($variant = $variant ?? 'full')
{{-- Правая колонка: сервис, реклама, Telegram. «Свежие вопросы», «Популярные статьи» и «Популярные категории» здесь не показываем --}}
@if ($variant !== 'tags' && \App\Models\SiteWidget::enabled('repair'))
    @include('public.partials.widgets.repair')
@endif
@include('public.partials.widgets.ad')
@if (request()->routeIs('home', 'articles.*', 'questions.show', 'firmwares.*', 'categories.show', 'tags.show') && \App\Models\SiteWidget::enabled('latest_discussions'))
    @include('public.partials.widgets.latest-comments')
@endif
@if ($variant !== 'tags' && $variant !== 'catalog' && \App\Models\SiteWidget::enabled('telegram'))
    @include('public.partials.widgets.telegram')
@endif
