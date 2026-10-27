@props(['active' => null])
{{-- Техника, по которой задают вопросы (/appliances/...). Под списком ссылка «Как это работает»:
     на компьютере отдельная карточка, на телефоне она лежит внутри выезжающей панели --}}
@php
    $how = route('how-it-works');
    $arrow = '<svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6l6 6-6 6" /></svg>';
@endphp
<x-ui.side-nav heading="Техника" label="Вид техники" :items="collect([['title' => 'Все вопросы', 'url' => route('questions.index'), 'active' => $active === null]])
    ->concat($popular_appliances->map(fn ($a) => ['title' => $a->title, 'url' => route('public.appliances.show', ['appliance' => $a->slug]), 'count' => $a->questions_count, 'active' => $active === $a->slug]))">
    <a href="{{ $how }}" class="keep flex items-center justify-between gap-3 text-[15px] font-bold text-black no-underline">Как это работает {!! $arrow !!}</a>
</x-ui.side-nav>
<a href="{{ $how }}" class="keep mt-4 hidden items-center justify-between gap-3 rounded-card bg-white px-5 py-4 text-[15px] font-bold text-black no-underline shadow-sm lg:flex">Как это работает {!! $arrow !!}</a>
