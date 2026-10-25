@props(['items' => [], 'dark' => false, 'light' => false]) {{-- [[title, url|null], ...]; dark - на чёрной подшапке, иначе на белом --}}
@php
    $dark = $dark || ! $light;
    $link = $dark ? 'text-white/70 hover:text-white' : 'text-subtle hover:text-black';
    $current = $dark ? 'text-white' : 'font-bold text-black';
    $sep = $dark ? 'text-white/40' : 'text-subtle/60';
    $parent = collect($items)->filter(fn ($i) => $i[1])->last();
@endphp
<nav aria-label="Хлебные крошки" {{ $attributes }}>
    {{-- Компьютер: полный путь --}}
    <ol class="flex flex-wrap items-center gap-x-2 text-[13px] leading-6 max-md:hidden">
        @foreach ($items as [$title, $url])
            <li class="flex min-w-0 items-center gap-2">
                @if ($url)
                    <a href="{{ $url }}" class="keep underline-offset-2 hover:underline {{ $link }}">{{ $title }}</a>
                @else
                    <span class="max-w-[40ch] truncate {{ $current }}" aria-current="page">{{ $title }}</span>
                @endif
                @unless ($loop->last)
                    <span class="{{ $sep }}" aria-hidden="true">›</span>
                @endunless
            </li>
        @endforeach
    </ol>
    {{-- Телефон: одна ссылка на родителя --}}
    @if ($parent)
        <a href="{{ $parent[1] }}" class="keep inline-flex items-center gap-1 text-[13px] leading-6 underline-offset-2 hover:underline md:hidden {{ $link }}">
            <span aria-hidden="true">‹</span> {{ $parent[0] }}
        </a>
    @endif
</nav>
