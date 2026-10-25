@php($variant = $variant ?? 'full')
@if ($variant === 'tags')
    @include('tw.partials.widgets.popular')
    @include('tw.partials.widgets.ad')
    @include('tw.partials.widgets.categories')
@else
    @include('tw.partials.widgets.telegram')
    @include('tw.partials.widgets.repair')
    @include('tw.partials.widgets.ad')
    @include('tw.partials.widgets.popular')
    @include('tw.partials.widgets.categories')
@endif
