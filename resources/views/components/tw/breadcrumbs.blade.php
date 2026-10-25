@props(['items' => []]) {{-- [[title, url|null], ...] --}}
<nav aria-label="Хлебные крошки" class="text-sm text-muted">
    <ol class="flex flex-wrap items-center gap-x-2 gap-y-1">
        @foreach ($items as [$title, $url])
            <li class="flex items-center gap-2">
                @if ($url)
                    <a href="{{ $url }}" class="hover:text-ink hover:underline">{{ $title }}</a>
                @else
                    <span class="line-clamp-1 text-ink" aria-current="page">{{ $title }}</span>
                @endif
                @unless ($loop->last)
                    <span aria-hidden="true">/</span>
                @endunless
            </li>
        @endforeach
    </ol>
</nav>
