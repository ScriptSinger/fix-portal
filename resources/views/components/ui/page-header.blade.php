@props(['title', 'items' => []])
{{-- Хлебные крошки (если переданы) стоят полосой над чёрным заголовком; в самой полосе только h1 --}}
@if ($items)
    <x-ui.breadcrumbs :items="$items" />
@endif
<div role="region" aria-label="Заголовок страницы" class="bg-black py-8 shadow-[inset_0_0_0_1px_#333] md:py-11">
    <div class="site-container">
        <h1 class="text-2xl font-bold leading-8 text-white">{{ $title }}</h1>
    </div>
</div>
