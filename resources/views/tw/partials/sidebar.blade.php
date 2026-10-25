<section class="rounded-xl bg-[#0088cc] p-5 text-white">
    <h2 class="font-bold">Telegram-чат сообщества</h2>
    <p class="mt-1 text-sm text-white/90">Делитесь опытом, задавайте вопросы, помогайте другим.</p>
    <a href="https://t.me/ufamasters_chat" target="_blank" rel="noopener"
        class="mt-4 block rounded-lg bg-white px-4 py-2.5 text-center text-sm font-bold text-[#0088cc] hover:bg-gray-100">Перейти
        в Telegram</a>
</section>

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
