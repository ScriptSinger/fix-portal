@props(['title', 'items' => []])
<div class="bg-black py-[51px] shadow-[inset_0_0_0_1px_#333]">
    <div class="mx-auto max-w-[1140px] px-[15px]">
        <div class="grid items-center gap-[30px] lg:grid-cols-[minmax(0,1fr)_350px]">
            <h1 class="text-2xl font-bold leading-6 text-white">{{ $title }}</h1>
            @if ($items)
                <x-tw.breadcrumbs :items="$items" class="max-lg:hidden [&_ol]:justify-end" />
            @endif
        </div>
    </div>
</div>
