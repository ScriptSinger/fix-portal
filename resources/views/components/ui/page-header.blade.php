@props(['title', 'items' => [], 'actions' => null])
{{-- Крошки строкой под шапкой, ниже заголовок страницы обычным текстом на сером фоне; справа слот actions для главного действия страницы --}}
@if ($items)
    <x-ui.breadcrumbs :items="$items" />
@endif
<div role="region" aria-label="Заголовок страницы" class="bg-surface pt-4">
    <div class="site-container flex flex-wrap items-center justify-between gap-x-6 gap-y-3">
        <h1 class="text-[28px] font-bold leading-9 text-black max-sm:text-2xl max-sm:leading-8">{{ $title }}</h1>
        @if ($actions)<div class="max-sm:w-full">{{ $actions }}</div>@endif
    </div>
</div>
