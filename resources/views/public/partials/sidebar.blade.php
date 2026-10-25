@php($variant = $variant ?? 'full')
@if ($variant === 'tags')
    @include('public.partials.widgets.popular')
    @include('public.partials.widgets.ad')
    @include('public.partials.widgets.categories')
@else
    @include('public.partials.widgets.telegram')
    @include('public.partials.widgets.repair')
    @include('public.partials.widgets.ad')
    @include('public.partials.widgets.popular')
    @include('public.partials.widgets.categories')
@endif
