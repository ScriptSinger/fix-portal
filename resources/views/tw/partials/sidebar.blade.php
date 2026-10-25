<x-tw.card class="border-l-4 border-l-[#0088cc]">
    <h2 class="flex items-center gap-2 font-sans font-bold">
        <svg class="size-5 text-[#0088cc]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21.9 4.3 2.7 11.7c-1.3.5-1.3 1.3-.2 1.6l4.9 1.5 1.9 5.8c.2.6.4.8.8.8.5 0 .7-.2 1-.5l2.4-2.3 4.9 3.6c.9.5 1.5.2 1.7-.8l3.2-15.1c.3-1.3-.5-1.9-1.4-1.5zM8.4 13.7l11-6.9c.5-.3 1-.1.6.2l-9 8.1-.4 3.8-2.2-5.2z"/></svg>
        Telegram-чат сообщества
    </h2>
    <p class="mt-1 text-sm text-muted">Делитесь опытом, задавайте вопросы, помогайте другим.</p>
    <x-tw.button variant="telegram" size="sm" href="https://t.me/ufamasters_chat" target="_blank" rel="noopener" class="mt-4 w-full">Перейти в Telegram</x-tw.button>
</x-tw.card>

<x-tw.widget title="Популярные статьи">
    <ul class="space-y-4">
        @foreach ($popular_posts as $post)
            <li class="flex gap-3">
                <img src="{{ $post->thumbnail?->small }}" alt="" loading="lazy"
                    class="size-16 shrink-0 rounded-md bg-surface object-cover">
                <div class="min-w-0">
                    <a href="{{ route('articles.show', ['article' => $post->slug]) }}"
                        class="line-clamp-2 text-sm font-medium leading-snug hover:underline">{{ $post->title }}</a>
                    <div class="mt-1 text-xs text-muted">{{ $post->dateAsCarbon->diffForHumans() }} ·
                        {{ $post->views }} просм.</div>
                </div>
            </li>
        @endforeach
    </ul>
</x-tw.widget>

<x-tw.widget title="Популярные категории">
    <ul class="divide-y divide-line text-sm">
        @foreach ($popular_categories as $category)
            <li>
                <a href="{{ route('categories.show', ['category' => $category->slug]) }}"
                    class="flex justify-between gap-3 py-2 hover:text-brand-700">
                    <span>{{ $category->title }}</span>
                    <span class="text-muted">{{ $category->posts_count }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</x-tw.widget>
