@props(['items' => [], 'light' => false]) {{-- [[title, url|null], ...]; light - на белом фоне, иначе на чёрной подшапке --}}
@php
    $link = $light ? 'text-brand-700' : 'text-brand-400';
    $dim = $light ? 'text-subtle' : 'text-muted';
@endphp
<nav aria-label="Хлебные крошки" {{ $attributes }}>
    <ol class="flex flex-wrap items-center gap-x-3 text-xs leading-[26px]">
        @foreach ($items as [$title, $url])
            <li class="flex items-center gap-3">
                @if ($url)
                    <a href="{{ $url }}" class="keep {{ $link }} hover:underline">{{ $title }}</a>
                @else
                    <span class="{{ $dim }}" aria-current="page">{{ $title }}</span>
                @endif
                @unless ($loop->last)
                    <span class="{{ $dim }}" aria-hidden="true">/</span>
                @endunless
            </li>
        @endforeach
    </ol>
</nav>
