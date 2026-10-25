@props(['post', 'showCategory' => false])
@php
    $url = route('articles.show', ['article' => $post->slug]);
    $sep = '<span class="text-[#ccc]" aria-hidden="true">/</span>';
@endphp
<article class="mb-8">
    <a href="{{ $url }}" class="group relative block overflow-hidden">
        <img src="{{ $post->thumbnail?->blog ?? asset('assets/front/upload/market_blog_01.jpg') }}"
            alt="{{ $post->title }}" loading="lazy"
            class="block w-full transition-transform duration-300 ease-in-out group-hover:scale-110">
        <span class="absolute inset-0 bg-[linear-gradient(0deg,#000_0%,rgba(0,0,0,.1)_50%)] opacity-50" aria-hidden="true"></span>
    </a>
    <div class="relative z-10 mx-12 -mt-16 rounded-[5px] bg-white pb-[59px] pt-12 text-center">
        <div class="mb-6"><x-tw.share :url="$url" /></div>
        <h2 class="pb-4 text-[28px] font-bold leading-[39.2px] text-black">
            <a href="{{ $url }}">{{ $post->title }}</a>
        </h2>
        <p class="mb-2 pb-[11.2px] pt-[4.8px]">{!! strip_tags($post->description) !!}</p>
        <div class="flex flex-wrap items-center justify-center gap-x-2 text-[11px] font-bold uppercase leading-[26px] text-[#111]">
            @if ($showCategory)
                <a href="{{ route('categories.show', ['category' => $post->category->slug]) }}"
                    class="font-normal text-[#999] hover:text-ink">{{ $post->category->title }}</a>
                {!! $sep !!}
            @endif
            <time datetime="{{ $post->dateAsCarbon->toDateString() }}">{{ $post->dateAsCarbon->diffForHumans() }}</time>
            {!! $sep !!}
            <span>{{ $post->administrator->name }}</span>
            {!! $sep !!}
            <span class="inline-flex items-center gap-1"><x-tw.icon name="eye" class="size-3" />{{ $post->views }}</span>
        </div>
    </div>
    <hr class="mx-12 mt-8 border-black/10">
</article>
