@php
    $currentYear = now()->year;
    $copyrightText = optional($customization)->copyright ?? "Copyright 2014-{$currentYear} Ufamasters.ru. Все права защищены.";
    $copyrightText = preg_replace('/(\b\d{4}-)\d{4}\b/u', '${1}' . $currentYear, $copyrightText);
@endphp
<footer class="mt-8 bg-ink text-gray-300">
    <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-3">
        <section>
            <h2 class="mb-4 border-b-2 border-brand-400 pb-2 text-sm font-bold uppercase tracking-wide text-white">
                Последние статьи</h2>
            <ul class="space-y-3 text-sm">
                @foreach ($latest_posts as $post)
                    <li>
                        <a href="{{ route('articles.show', ['article' => $post->slug]) }}"
                            class="hover:text-brand-400">{{ $post->title }}</a>
                        <div class="text-xs text-gray-500">{{ $post->dateAsCarbon->diffForHumans() }}</div>
                    </li>
                @endforeach
            </ul>
        </section>
        <section>
            <h2 class="mb-4 border-b-2 border-brand-400 pb-2 text-sm font-bold uppercase tracking-wide text-white">
                Популярные статьи</h2>
            <ul class="space-y-3 text-sm">
                @foreach ($popular_posts as $post)
                    <li>
                        <a href="{{ route('articles.show', ['article' => $post->slug]) }}"
                            class="hover:text-brand-400">{{ $post->title }}</a>
                        <div class="text-xs text-gray-500">{{ $post->views }} просмотров</div>
                    </li>
                @endforeach
            </ul>
        </section>
        <section>
            <h2 class="mb-4 border-b-2 border-brand-400 pb-2 text-sm font-bold uppercase tracking-wide text-white">
                Категории</h2>
            <ul class="space-y-2 text-sm">
                @foreach ($popular_categories as $category)
                    <li class="flex justify-between gap-3">
                        <a href="{{ route('categories.show', ['category' => $category->slug]) }}"
                            class="hover:text-brand-400">{{ $category->title }}</a>
                        <span class="text-gray-500">{{ $category->posts_count }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
    <div class="border-t border-white/10 py-5 text-center text-xs text-gray-500">&copy; {{ $copyrightText }}</div>
</footer>
