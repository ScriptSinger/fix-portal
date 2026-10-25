@props(['title', 'items' => []])
<div role="region" aria-label="Заголовок страницы" class="bg-black py-[51px] shadow-[inset_0_0_0_1px_#333]">
    <div class="mx-auto sm:max-w-[540px] md:max-w-[720px] lg:max-w-[960px] xl:max-w-[1140px] px-[15px]">
        <div class="-mx-[15px] grid items-center md:grid-cols-[2fr_1fr]">
            <h1 class="px-[15px] text-2xl font-bold leading-6 text-white">{{ $title }}</h1>
            @if ($items)
                <x-tw.breadcrumbs :items="$items" class="max-md:hidden px-[15px] [&_ol]:justify-end" />
            @endif
        </div>
    </div>
</div>
