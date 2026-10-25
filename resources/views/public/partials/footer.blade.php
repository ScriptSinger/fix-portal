@php
    $currentYear = now()->year;
    $copyrightText = optional($customization)->copyright ?? "Copyright 2014-{$currentYear} Ufamasters.ru. Все права защищены.";
    $copyrightText = preg_replace('/(\b\d{4}-)\d{4}\b/u', '${1}' . $currentYear, $copyrightText);
    $title = 'mb-8 pb-2 text-lg font-bold capitalize leading-[18px] text-white';
@endphp
<footer class="border-[6px] border-black bg-black py-16 shadow-[inset_0_0_0_1px_#333]">
    <div class="mx-auto sm:max-w-[540px] md:max-w-[720px] lg:max-w-[960px] xl:max-w-[1140px] px-[15px]">
        <div class="-mx-[15px] grid lg:grid-cols-3">
            @foreach ([['Последние статьи', $latest_posts, false], ['Популярные статьи', $popular_posts, true]] as [$heading, $posts, $showViews])
                <section class="px-[15px] max-lg:mb-12 max-lg:mt-8">
                    <h2 class="{{ $title }}">{{ $heading }}</h2>
                    <ul class="space-y-4">
                        @foreach ($posts as $post)
                            <li>
                                <a href="{{ route('articles.show', ['article' => $post->slug]) }}" class="flex items-start gap-4">
                                    <img src="{{ optional($post->thumbnail)->small }}" alt="" loading="lazy"
                                        class="size-20 shrink-0 border-4 border-[#333] object-cover">
                                    <span class="min-w-0">
                                        <span class="block text-base leading-6 text-white">{{ $post->title }}</span>
                                        <small class="text-[10px] uppercase tracking-[1px] text-brand-400">
                                            @if ($showViews)
                                                <x-ui.icon name="eye" class="inline size-3" /> {{ $post->views }}
                                            @else
                                                {{ $post->dateAsCarbon->diffForHumans() }}
                                            @endif
                                        </small>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach

            <section class="px-[15px] max-lg:mb-12 max-lg:mt-8">
                <h2 class="{{ $title }}">Популярные категории</h2>
                <ul>
                    @foreach ($popular_categories as $category)
                        <li class="border-b border-dashed border-[#333] pb-[9.6px] pl-[3.2px] pr-[3.2px] pt-[6.4px] first:pt-0 last:border-0 max-md:py-0">
                            <a href="{{ route('categories.show', ['category' => $category->slug]) }}"
                                class="flex justify-between gap-3 text-[15px] capitalize leading-[15px] text-white hover:text-brand-400 max-md:py-[13px]">
                                <span>{{ $category->title }}</span><span>{{ $category->posts_count }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>

        <p class="mt-16 text-center text-[#9a9a9a]">&copy; {{ $copyrightText }}</p>
    </div>
</footer>
