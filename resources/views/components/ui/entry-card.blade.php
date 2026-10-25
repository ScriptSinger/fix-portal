@props(['url', 'image', 'full' => null, 'title', 'excerpt', 'date', 'author', 'views', 'category' => null, 'categoryUrl' => null])
@php $sep = '<span class="text-soft" aria-hidden="true">/</span>'; @endphp
<article class="mb-8">
    <a href="{{ $url }}" data-lightbox="{{ $full ?: $image }}" class="group relative block cursor-pointer overflow-hidden">
        <img src="{{ $image }}" alt="{{ $title }}" loading="lazy"
            class="block w-full transition-transform duration-300 ease-in-out group-hover:scale-110">
        <span class="absolute inset-0 bg-[linear-gradient(0deg,#000_0%,rgba(0,0,0,.1)_50%)] opacity-50" aria-hidden="true"></span>
    </a>
    <div class="relative z-10 mx-12 -mt-16 rounded-[5px] bg-white pb-[51px] pt-12 text-center">
        <div class="mb-8"><x-ui.share :url="$url" /></div>
        <h2 class="pb-4 text-[28px] font-bold leading-[39.2px] text-black">
            <a href="{{ $url }}">{{ $title }}</a>
        </h2>
        <p class="mb-2 pb-[11.2px] pt-[4.8px]">{!! $excerpt !!}</p>
        <div class="flex flex-wrap items-center justify-center gap-x-2 text-[11px] font-bold uppercase leading-[26px] text-ink">
            @if ($category)
                <a href="{{ $categoryUrl }}" class="-my-2 inline-block py-2 font-normal text-subtle hover:text-ink">{{ $category }}</a>
                {!! $sep !!}
            @endif
            <time datetime="{{ $date->toDateString() }}">{{ $date->diffForHumans() }}</time>
            {!! $sep !!}
            <span>{{ $author }}</span>
            {!! $sep !!}
            <span class="inline-flex items-center gap-1"><x-ui.icon name="eye" class="size-3" />{{ $views }}</span>
        </div>
    </div>
    <hr class="mx-12 mt-8 border-black/10">
</article>
