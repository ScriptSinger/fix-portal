@if ($paginator->hasPages())
    @php
        $item = 'keep inline-flex h-10 min-w-10 items-center justify-center border border-black px-3 text-sm leading-none transition-colors';
        $idle = 'bg-white text-ink hover:bg-brand-400';
    @endphp
    <nav role="navigation" aria-label="Пагинация" class="flex flex-wrap justify-center gap-1.5">
        @if ($paginator->onFirstPage())
            <span class="{{ $item }} border-line bg-transparent text-soft" aria-hidden="true">&lsaquo;</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Назад" class="{{ $item }} {{ $idle }}">&lsaquo;</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="{{ $item }} border-transparent text-subtle">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="{{ $item }} bg-black text-brand-400" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="{{ $item }} {{ $idle }} max-sm:hidden">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Вперёд" class="{{ $item }} {{ $idle }}">&rsaquo;</a>
        @else
            <span class="{{ $item }} border-line bg-transparent text-soft" aria-hidden="true">&rsaquo;</span>
        @endif
    </nav>
@endif
