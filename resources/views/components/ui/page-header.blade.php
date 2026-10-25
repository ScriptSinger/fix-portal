@props(['title', 'items' => []])
<div role="region" aria-label="Заголовок страницы" class="bg-black py-10 shadow-[inset_0_0_0_1px_#333] md:py-[51px]">
    <div class="site-container">
        @if ($items)
            <x-ui.breadcrumbs dark :items="$items" class="mb-3" />
        @endif
        <h1 class="text-2xl font-bold leading-8 text-white">{{ $title }}</h1>
    </div>
</div>
