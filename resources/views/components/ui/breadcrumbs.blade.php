@props(['items' => []]) {{-- [[title, url|null], ...] --}}
<nav aria-label="Хлебные крошки" {{ $attributes }}>
    <ol class="flex flex-wrap items-center gap-x-3 text-xs leading-[26px]">
        @foreach ($items as [$title, $url])
            <li class="flex items-center gap-3">
                @if ($url)
                    <a href="{{ $url }}" class="keep text-brand-400 hover:underline">{{ $title }}</a>
                @else
                    <span class="text-muted" aria-current="page">{{ $title }}</span>
                @endif
                @unless ($loop->last)
                    <span class="text-muted" aria-hidden="true">/</span>
                @endunless
            </li>
        @endforeach
    </ol>
</nav>
