<x-ui.widget title="Популярные статьи">
    <ul>
        @foreach ($popular_posts as $post)
            <li class="pb-4">
                <a href="{{ route('articles.show', ['article' => $post->slug]) }}" class="block overflow-hidden">
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
