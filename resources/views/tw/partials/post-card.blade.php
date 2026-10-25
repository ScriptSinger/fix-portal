@php $url = route('articles.show', ['article' => $post->slug]); @endphp
<article class="group overflow-hidden rounded-xl border border-line bg-white shadow-sm transition-shadow hover:shadow-md">
    <a href="{{ $url }}" class="block aspect-[16/9] overflow-hidden bg-surface">
        <img src="{{ $post->thumbnail?->blog ?? asset('assets/front/upload/market_blog_01.jpg') }}"
            alt="{{ $post->title }}" loading="lazy"
            class="size-full object-cover transition-transform duration-300 group-hover:scale-105">
    </a>
    <div class="p-5">
        <a href="{{ route('categories.show', ['category' => $post->category->slug]) }}"
            class="text-xs font-bold uppercase tracking-wide text-brand-700 hover:underline">{{ $post->category->title }}</a>
        <h2 class="mt-1 text-xl font-bold leading-snug">
            <a href="{{ $url }}" class="hover:underline">{{ $post->title }}</a>
        </h2>
        <p class="mt-2 line-clamp-3 text-[15px] leading-relaxed text-muted">{!! strip_tags($post->description) !!}</p>
        <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-muted">
            <span>{{ $post->dateAsCarbon->diffForHumans() }}</span>
            <span>{{ $post->administrator->name }}</span>
            <span class="inline-flex items-center gap-1">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
                {{ $post->views }}
            </span>
        </div>
    </div>
</article>
