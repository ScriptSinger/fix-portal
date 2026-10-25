@props(['items' => [], 'dark' => false]) {{-- [[title, url|null], ...] --}}
<nav aria-label="Хлебные крошки" @class(['text-sm', 'text-muted' => !$dark, 'text-gray-400' => $dark])>
    <ol class="flex flex-wrap items-center gap-x-2 gap-y-1">
        @foreach ($items as [$title, $url])
            <li class="flex items-center gap-2">
                @if ($url)
                    <a href="{{ $url }}" @class([
                        'hover:underline',
                        'hover:text-ink' => !$dark,
                        'text-brand-400' => $dark,
                    ])>{{ $title }}</a>
                @else
                    <span @class(['line-clamp-1', 'text-ink' => !$dark, 'text-gray-200' => $dark]) aria-current="page">{{ $title }}</span>
                @endif
                @unless ($loop->last)
                    <span aria-hidden="true">/</span>
                @endunless
            </li>
        @endforeach
    </ol>
</nav>
