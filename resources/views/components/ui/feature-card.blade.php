@props(['url', 'image', 'title', 'category' => null, 'date', 'author', 'views', 'big' => false])
{{-- Крупная карточка с картинкой на весь фон; заголовок читается на тёмном градиенте --}}
<a href="{{ $url }}" {{ $attributes->merge(['class' => 'group relative flex items-end overflow-hidden bg-[#222] text-white ' . ($big ? 'min-h-[320px] lg:row-span-2 lg:min-h-0' : 'min-h-[200px]')]) }}>
    <img src="{{ $image }}" alt="" loading="{{ $big ? 'eager' : 'lazy' }}"
        class="absolute inset-0 size-full object-cover transition-transform duration-300 group-hover:scale-105">
    <span class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/35 to-transparent" aria-hidden="true"></span>
    <span class="relative block p-5 sm:p-6">
        @if ($category)
            <span class="block w-fit max-w-full truncate bg-brand-400 px-2.5 py-1 text-[11px] font-bold uppercase leading-4 tracking-[1.5px] text-black">{{ $category }}</span>
        @endif
        <h2 class="mt-2 font-bold text-white {{ $big ? 'text-2xl leading-8 sm:text-[30px] sm:leading-[38px]' : 'text-[19px] leading-[26px]' }}">{{ $title }}</h2>
        <span class="mt-1 block text-xs leading-5 text-white/80">{{ $date->diffForHumans() }} · {{ $author }} · {{ $views }} просм.</span>
    </span>
</a>
