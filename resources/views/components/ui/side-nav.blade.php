@props(['heading', 'label', 'items'])
{{-- Левая навигация раздела: items = [['title' => , 'url' => , 'active' => bool], ...].
     На компьютере карточка как правые виджеты (заголовок и список с пунктирными разделителями),
     на телефоне и планшете строка чипов с прокруткой. Счётчики не показываем. --}}
@php
    $chip = 'keep inline-flex h-10 shrink-0 items-center border border-black px-4 text-[13px] leading-none';
    $row = 'lg:flex lg:h-auto lg:border-0 lg:px-0 lg:py-3 lg:text-[15px] lg:font-bold lg:leading-[22px]';
@endphp
<nav aria-label="{{ $label }}" class="lg:rounded-[5px] lg:bg-white lg:p-5">
    <h2 class="mb-4 hidden text-lg font-bold leading-[18px] text-ink lg:block">{{ $heading }}</h2>
    <ul class="-mx-[15px] flex gap-2 overflow-x-auto px-[15px] pb-1 lg:mx-0 lg:block lg:overflow-visible lg:px-0 lg:pb-0">
        @foreach ($items as $item)
            <li class="shrink-0 lg:block lg:border-b lg:border-dashed lg:border-line lg:first:pt-0 lg:last:border-0">
                <a href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif
                    class="{{ $chip }} {{ $row }} {{ $item['active'] ? 'bg-black text-brand-400 lg:-mx-3 lg:px-3' : 'bg-white text-ink hover:text-brand-700 lg:bg-transparent' }}">
                    <span>{{ $item['title'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</nav>
