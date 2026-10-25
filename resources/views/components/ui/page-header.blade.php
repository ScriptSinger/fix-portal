@props(['title', 'items' => []])
{{-- Крошки строкой под шапкой, ниже заголовок страницы обычным текстом на сером фоне --}}
@if ($items)
    <x-ui.breadcrumbs :items="$items" />
@endif
<div role="region" aria-label="Заголовок страницы" class="bg-surface pt-4">
    <div class="site-container">
        <h1 class="text-[28px] font-bold leading-9 text-black max-sm:text-2xl max-sm:leading-8">{{ $title }}</h1>
    </div>
</div>
