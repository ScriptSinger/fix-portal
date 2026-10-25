@props(['active' => null])
{{-- Слева на компьютере: список разделов; на телефоне и планшете: строка чипов с прокруткой --}}
@php
    $chip = 'keep inline-flex h-10 shrink-0 items-center border border-black px-4 text-[13px] leading-none';
@endphp
<nav aria-label="Категории статей">
    <h2 class="mb-3 hidden text-[13px] font-bold uppercase tracking-[1.5px] text-ink lg:block">Разделы</h2>
    <ul class="-mx-[15px] flex gap-2 overflow-x-auto px-[15px] pb-1 lg:mx-0 lg:block lg:space-y-1 lg:overflow-visible lg:px-0 lg:pb-0">
        <li class="shrink-0 lg:block">
            <a href="{{ route('articles.index') }}" @if ($active === null) aria-current="page" @endif
                class="{{ $chip }} {{ $active === null ? 'bg-black text-brand-400' : 'bg-white text-ink hover:bg-brand-400' }} lg:flex lg:h-auto lg:justify-between lg:border-0 lg:px-3 lg:py-[9px] lg:text-sm lg:leading-5 {{ $active === null ? '' : 'lg:bg-transparent lg:hover:bg-white' }}">
                Все статьи</a>
        </li>
        @foreach ($popular_categories as $category)
            @php($on = $active === $category->slug)
            <li class="shrink-0 lg:block">
                <a href="{{ route('categories.show', ['category' => $category->slug]) }}" @if ($on) aria-current="page" @endif
                    class="{{ $chip }} {{ $on ? 'bg-black text-brand-400' : 'bg-white text-ink hover:bg-brand-400' }} lg:flex lg:h-auto lg:justify-between lg:gap-3 lg:border-0 lg:px-3 lg:py-[9px] lg:text-sm lg:leading-5 {{ $on ? '' : 'lg:bg-transparent lg:hover:bg-white' }}">
                    <span>{{ $category->title }}</span><span class="max-lg:hidden {{ $on ? 'text-brand-400' : 'text-subtle' }}">{{ $category->posts_count }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</nav>
