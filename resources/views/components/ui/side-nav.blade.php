@props(['heading', 'label', 'items'])
{{-- Левая навигация раздела: items = [['title' => , 'url' => , 'count' => , 'active' => bool], ...].
     На компьютере вертикальный список, на телефоне и планшете строка чипов с прокруткой. --}}
@php
    $chip = 'keep inline-flex h-10 shrink-0 items-center border border-black px-4 text-[13px] leading-none';
    $row = 'lg:flex lg:h-auto lg:justify-between lg:gap-3 lg:border-0 lg:px-3 lg:py-[9px] lg:text-sm lg:leading-5';
@endphp
<nav aria-label="{{ $label }}" class="lg:rounded-[5px] lg:bg-white lg:p-3">
    <h2 class="mb-2 hidden px-3 pt-2 text-[13px] font-bold uppercase tracking-[1.5px] text-ink lg:block">{{ $heading }}</h2>
    <ul class="-mx-[15px] flex gap-2 overflow-x-auto px-[15px] pb-1 lg:mx-0 lg:block lg:space-y-1 lg:overflow-visible lg:px-0 lg:pb-0">
        @foreach ($items as $item)
            <li class="shrink-0 lg:block">
                <a href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif
                    class="{{ $chip }} {{ $row }} {{ $item['active'] ? 'bg-black text-brand-400' : 'bg-white text-ink hover:bg-brand-400 lg:bg-transparent lg:hover:bg-brand-400' }}">
                    <span>{{ $item['title'] }}</span>
                    @isset($item['count'])<span class="max-lg:hidden {{ $item['active'] ? 'text-brand-400' : 'text-subtle' }}">{{ $item['count'] }}</span>@endisset
                </a>
            </li>
        @endforeach
    </ul>
</nav>
