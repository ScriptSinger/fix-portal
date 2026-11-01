@props(['url', 'image' => null, 'title', 'excerpt', 'date', 'author', 'views', 'category' => null, 'categoryUrl' => null, 'authorProfileUrl' => null, 'placeholder' => false, 'images' => [], 'rounded' => true, 'solved' => false, 'answers' => null, 'stackMobile' => false])
{{-- Карточка записи для сетки: на телефоне компактная строка (миниатюра слева), от sm карточка с картинкой сверху.
     placeholder: если у записи нет картинки (или она не загрузилась), вместо неё светлая плитка с жёлтым «?». --}}
<article @class([
    'group relative isolate flex bg-white sm:flex-col',
    'max-sm:flex-col' => $stackMobile || count($images) > 1,
    'overflow-hidden rounded-card shadow-sm max-sm:rounded-[18px] max-sm:shadow-[0_1px_2px_rgba(0,0,0,.14)]' => $rounded,
])>
    @if ($image || $placeholder)
        <a href="{{ $url }}" tabindex="-1" aria-hidden="true"
            class="relative z-[1] block aspect-square w-[96px] shrink-0 overflow-hidden {{ $placeholder ? 'bg-brand-50' : 'bg-surface' }} {{ $stackMobile || count($images) > 1 ? 'max-sm:aspect-[16/10] max-sm:w-full max-sm:rounded-t-[18px] sm:rounded-none' : 'rounded-l-[18px] sm:rounded-none' }} sm:aspect-[16/10] sm:w-full">
            @if ($placeholder)
                <span class="absolute inset-0 grid place-items-center text-[44px] font-bold leading-none text-brand-500 sm:text-[56px]">?</span>
            @endif
            @if (count($images) > 1)
                {{-- Несколько фото: мозаика как в Telegram, до трёх плиток --}}
                @php $tiles = array_slice($images, 0, 3); @endphp
                <div @class(['absolute inset-0 grid gap-0.5', 'grid-cols-2' => count($tiles) === 2, 'grid-cols-2 grid-rows-2' => count($tiles) === 3])>
                    @foreach ($tiles as $n => $tile)
                        <img src="{{ $tile }}" alt="" loading="lazy" onerror="const a = this.closest('a'); this.remove(); if (!a.querySelector('img, span')) a.remove()"
                            @class(['block size-full min-h-0 object-cover', 'col-span-2' => count($tiles) === 3 && $n === 0])>
                    @endforeach
                </div>
            @elseif ($image)
                <img src="{{ $image }}" alt="" loading="lazy" onerror="const a = this.closest('a'); this.remove(); if (!a.querySelector('img, span')) a.remove()"
                    class="absolute inset-0 block size-full object-cover transition-transform duration-300 group-hover:scale-105">
            @endif
        </a>
    @endif
    <div class="relative z-[1] flex min-w-0 flex-1 flex-col gap-1.5 p-3 sm:gap-2 sm:p-5">
        @if ($category || $solved)
            <div class="-my-1 flex items-center gap-2 py-1 text-xs leading-4">
                @if ($category)<a href="{{ $categoryUrl }}" class="min-w-0 truncate text-[12px] text-subtle hover:text-ink">{{ $category }}</a>@endif
                @if ($solved)<span class="shrink-0 font-bold text-green-700">✓ Решён</span>@endif
            </div>
        @endif
        <h2 class="line-clamp-2 text-[15px] font-bold leading-[20px] text-black sm:text-[19px] sm:leading-[26px]"><a href="{{ $url }}">{{ $title }}</a></h2>
        <p class="line-clamp-2 text-[13px] leading-[18px] sm:line-clamp-4 sm:text-sm sm:leading-[22px]">{!! $excerpt !!}</p>
        <div class="mt-auto flex flex-wrap items-center gap-x-1.5 pt-0.5 text-[11px] leading-4 text-subtle sm:gap-x-2 sm:pt-1 sm:text-xs sm:leading-5">
            <time datetime="{{ $date->toDateString() }}">{{ $date->diffForHumans() }}</time>
            <span aria-hidden="true">·</span>
            @if ($authorProfileUrl)
                <button type="button" data-user-profile-url="{{ $authorProfileUrl }}" class="truncate text-left hover:text-ink hover:underline focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-brand-500" aria-label="Открыть профиль: {{ $author }}">{{ $author }}</button>
            @else
                <span class="truncate">{{ $author }}</span>
            @endif
            @if ($answers !== null)
                <span aria-hidden="true">·</span>
                <span class="shrink-0 whitespace-nowrap">{{ $answers }} {{ trans_choice('ответ|ответа|ответов', $answers, [], 'ru') }}</span>
            @endif
            <span aria-hidden="true">·</span>
            <span class="inline-flex shrink-0 items-center gap-1 whitespace-nowrap" aria-label="Просмотров: {{ $views }}"><x-ui.icon name="eye" class="size-3" aria-hidden="true" />{{ $views }}</span>
        </div>
    </div>
</article>
