@props(['url', 'image' => null, 'title', 'excerpt', 'date', 'author', 'views', 'category' => null, 'categoryUrl' => null, 'placeholder' => false, 'images' => [], 'rounded' => true, 'solved' => false])
{{-- Карточка записи для сетки: на телефоне компактная строка (миниатюра слева), от sm карточка с картинкой сверху.
     placeholder: если у записи нет картинки (или она не загрузилась), вместо неё светлая плитка с жёлтым «?». --}}
<article @class(['group flex bg-white sm:flex-col', 'overflow-hidden rounded-card shadow-sm' => $rounded])>
    @if ($image || $placeholder)
        <a href="{{ $url }}" tabindex="-1" aria-hidden="true"
            class="relative block aspect-square w-[104px] shrink-0 overflow-hidden {{ $placeholder ? 'bg-brand-50' : 'bg-surface' }} sm:aspect-[16/10] sm:w-full">
            @if ($placeholder)
                <span class="absolute inset-0 grid place-items-center text-[44px] font-bold leading-none text-brand-500 sm:text-[56px]">?</span>
            @endif
            @if (count($images) > 1)
                {{-- Несколько фото: мозаика как в Telegram, до трёх плиток --}}
                @php $tiles = array_slice($images, 0, 3); @endphp
                <div @class(['absolute inset-0 grid gap-0.5', 'grid-cols-2' => count($tiles) === 2, 'grid-cols-[2fr_1fr] grid-rows-2' => count($tiles) === 3])>
                    @foreach ($tiles as $n => $tile)
                        <img src="{{ $tile }}" alt="" loading="lazy" onerror="const a = this.closest('a'); this.remove(); if (!a.querySelector('img, span')) a.remove()"
                            @class(['block size-full min-h-0 object-cover', 'row-span-2' => count($tiles) === 3 && $n === 0])>
                    @endforeach
                </div>
            @elseif ($image)
                <img src="{{ $image }}" alt="" loading="lazy" onerror="const a = this.closest('a'); this.remove(); if (!a.querySelector('img, span')) a.remove()"
                    class="absolute inset-0 block size-full object-cover transition-transform duration-300 group-hover:scale-105">
            @endif
        </a>
    @endif
    <div class="flex min-w-0 flex-1 flex-col gap-2 p-3 sm:p-5">
        @if ($category || $solved)
            <div class="-my-1 flex items-center gap-2 py-1 text-xs leading-4">
                @if ($category)<a href="{{ $categoryUrl }}" class="min-w-0 truncate text-subtle hover:text-ink">{{ $category }}</a>@endif
                @if ($solved)<span class="shrink-0 font-bold text-green-700">✓ Решён</span>@endif
            </div>
        @endif
        <h2 class="text-base font-bold leading-[22px] text-black sm:text-[19px] sm:leading-[26px]"><a href="{{ $url }}">{{ $title }}</a></h2>
        <p class="{{ $image || $placeholder ? 'line-clamp-2 max-sm:hidden' : 'line-clamp-6' }} text-sm leading-[22px]">{!! $excerpt !!}</p>
        <div class="mt-auto flex flex-wrap items-center gap-x-2 pt-1 text-xs leading-5 text-subtle">
            <time datetime="{{ $date->toDateString() }}">{{ $date->diffForHumans() }}</time>
            <span aria-hidden="true">·</span>
            <span>{{ $author }}</span>
            <span aria-hidden="true">·</span>
            <span class="inline-flex items-center gap-1"><x-ui.icon name="eye" class="size-3" />{{ $views }}</span>
        </div>
    </div>
</article>
