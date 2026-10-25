<x-ui.widget title="Популярные статьи">
    <ul>
        @foreach ($popular_posts as $post)
            <li>
                <a href="{{ route('articles.show', ['article' => $post->slug]) }}" class="keep -mx-5 block overflow-hidden px-5 py-2 transition-colors hover:bg-brand-50 hover:shadow-[inset_4px_0_0_var(--color-brand-400)]">
                    <img src="{{ $post->thumbnail?->small }}" alt="" loading="lazy" class="float-left mr-[15px] size-[55px] bg-[#333] object-cover">
                    <h3 class="text-[15px] font-bold leading-[22.5px] text-black">{{ $post->title }}</h3>
                    <small class="text-[10px] font-medium uppercase leading-[26px] tracking-[1px] text-brand-700">
                        {{ $post->dateAsCarbon->diffForHumans() }} | <x-ui.icon name="eye" class="inline size-3" /> {{ $post->views }}
                    </small>
                </a>
            </li>
        @endforeach
    </ul>
</x-ui.widget>
