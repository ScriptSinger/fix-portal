@if ($paginator->hasPages())
    @php
        $item = 'inline-flex min-w-10 items-center justify-center rounded-md border px-3 py-2 text-sm font-medium';
    @endphp
    <nav role="navigation" aria-label="Пагинация" class="flex flex-wrap justify-center gap-1.5">
        @if ($paginator->onFirstPage())
            <span class="{{ $item }} border-line bg-white text-gray-300" aria-hidden="true">&lsaquo;</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Назад"
                class="{{ $item }} border-line bg-white hover:bg-brand-50">&lsaquo;</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="{{ $item }} hidden border-transparent text-muted sm:inline-flex">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="{{ $item }} border-brand-400 bg-brand-400 text-ink"
                            aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}"
                            class="{{ $item }} hidden border-line bg-white hover:bg-brand-50 sm:inline-flex">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Вперёд"
                class="{{ $item }} border-line bg-white hover:bg-brand-50">&rsaquo;</a>
        @else
            <span class="{{ $item }} border-line bg-white text-gray-300" aria-hidden="true">&rsaquo;</span>
        @endif
    </nav>
@endif
