@if ($paginator->hasPages())
    @php
        $item = 'inline-flex h-10 min-w-[30px] items-center justify-center bg-brand-400 px-[12.8px] text-sm leading-[14px] text-ink transition-colors';
    @endphp
    <nav role="navigation" aria-label="Пагинация" class="flex flex-wrap justify-center">
        @if ($paginator->onFirstPage())
            <span class="{{ $item }} rounded-l-[4px] opacity-60" aria-hidden="true">&lsaquo;</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Назад"
                class="{{ $item }} rounded-l-[4px] hover:bg-[#c89700]">&lsaquo;</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="{{ $item }} opacity-60">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="{{ $item }} border border-black" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="{{ $item }} hover:bg-[#c89700] max-sm:hidden">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Вперёд"
                class="{{ $item }} rounded-r-[4px] hover:bg-[#c89700]">&rsaquo;</a>
        @else
            <span class="{{ $item }} rounded-r-[4px] opacity-60" aria-hidden="true">&rsaquo;</span>
        @endif
    </nav>
@endif
