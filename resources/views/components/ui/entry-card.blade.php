@props(['url', 'image' => null, 'title', 'excerpt', 'date', 'author', 'views', 'category' => null, 'categoryUrl' => null])
{{-- Карточка записи для сетки: на телефоне компактная строка (миниатюра слева), от sm карточка с картинкой сверху --}}
<article class="group flex bg-white sm:flex-col">
    @if ($image)
        <a href="{{ $url }}" tabindex="-1" aria-hidden="true" class="block w-[104px] shrink-0 overflow-hidden sm:w-full">
            <img src="{{ $image }}" alt="" loading="lazy"
                class="block aspect-square size-full object-cover transition-transform duration-300 group-hover:scale-105 sm:aspect-[16/10]">
        </a>
    @endif
    <div class="flex min-w-0 flex-1 flex-col gap-2 p-3 sm:p-5">
        @if ($category)
            <a href="{{ $categoryUrl }}" class="-my-1 max-w-full self-start truncate py-1 text-[11px] uppercase leading-4 tracking-[1.5px] text-subtle hover:text-ink">{{ $category }}</a>
        @endif
        <h2 class="text-base font-bold leading-[22px] text-black sm:text-[19px] sm:leading-[26px]"><a href="{{ $url }}">{{ $title }}</a></h2>
        <p class="line-clamp-2 text-sm leading-[22px] max-sm:hidden">{!! $excerpt !!}</p>
        <div class="mt-auto flex flex-wrap items-center gap-x-2 pt-1 text-xs leading-5 text-subtle">
            <time datetime="{{ $date->toDateString() }}">{{ $date->diffForHumans() }}</time>
            <span aria-hidden="true">·</span>
            <span>{{ $author }}</span>
            <span aria-hidden="true">·</span>
            <span class="inline-flex items-center gap-1"><x-ui.icon name="eye" class="size-3" />{{ $views }}</span>
        </div>
    </div>
</article>
