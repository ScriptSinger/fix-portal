@php($variant = $variant ?? 'full')
{{-- Правая колонка: действие, реклама, популярное. Категории показываем здесь, только если слева нет своей навигации --}}
@if ($variant === 'catalog')
    @include('public.partials.widgets.repair')
    @include('public.partials.widgets.ad')
@else
    @unless (request()->routeIs('questions.*', 'public.appliances.*'))
        @include('public.partials.widgets.questions')
    @endunless
    @if ($variant !== 'tags')
        @include('public.partials.widgets.repair')
    @endif
    @include('public.partials.widgets.ad')
    @include('public.partials.widgets.popular')
    @if ($variant !== 'tags')
        @include('public.partials.widgets.telegram')
    @endif
    @unless (View::hasSection('sidebar-left'))
        @include('public.partials.widgets.categories')
    @endunless
@endif
