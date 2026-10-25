@props(['post'])
@php $url = route('articles.show', ['article' => $post->slug]); @endphp
<article class="group flex flex-col overflow-hidden rounded-xl border border-line bg-white shadow-sm transition-shadow hover:shadow-md">
    <a href="{{ $url }}" class="block aspect-[16/9] overflow-hidden bg-surface">
        <img src="{{ $post->thumbnail?->blog ?? asset('assets/front/upload/market_blog_01.jpg') }}"
            alt="{{ $post->title }}" loading="lazy"
            class="size-full object-cover transition-transform duration-300 group-hover:scale-105">
    </a>
    <div class="flex flex-1 flex-col p-5">
        <a href="{{ route('categories.show', ['category' => $post->category->slug]) }}"
            class="text-xs font-bold uppercase tracking-wide text-brand-700 hover:underline">{{ $post->category->title }}</a>
        <h2 class="mt-1 font-sans text-xl font-bold leading-snug">
            <a href="{{ $url }}" class="hover:underline">{{ $post->title }}</a>
        </h2>
        <p class="mt-2 line-clamp-3 text-[15px] leading-relaxed text-muted">{!! strip_tags($post->description) !!}</p>
        <div class="mt-auto flex flex-wrap items-center pt-4 gap-x-4 gap-y-1 text-[13px] text-muted">
            <time datetime="{{ $post->dateAsCarbon->toDateString() }}">{{ $post->dateAsCarbon->translatedFormat('j F Y') }}</time>
            <span>{{ $post->administrator->name }}</span>
            <x-tw.views :count="$post->views" />
        </div>
    </div>
</article>
