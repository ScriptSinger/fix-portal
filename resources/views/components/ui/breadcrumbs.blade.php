@props(['items' => []])
{{-- Хлебные крошки: одна полоса под шапкой на всех страницах. Первое звено «Главная» добавляется здесь, вызывающий передаёт
     остальные [[название, адрес|null], ...]; последнее звено это текущая страница (адрес null). Видимый список и разметка
     BreadcrumbList для поисковиков строятся из одного массива, поэтому разойтись не могут. --}}
@php
    $trail = collect($items)
        ->reject(fn ($i) => in_array($i[0], ['Главная', 'Статьи'], true) && ($i[1] ?? null) === route('articles.index'))
        ->prepend(['Главная', route('articles.index')])
        ->values();
    $ld = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $trail->map(fn ($i, $n) => [
            '@type' => 'ListItem', 'position' => $n + 1, 'name' => $i[0], 'item' => $i[1] ?? url()->current(),
        ])->all(),
    ];
@endphp
<script type="application/ld+json">@json($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)</script>
<div class="border-b border-line bg-white" data-breadcrumbs>
    <nav aria-label="Хлебные крошки" class="site-container">
        <ol class="flex items-center gap-x-2 overflow-x-auto whitespace-nowrap py-2.5 text-[13px] leading-6">
            @foreach ($trail as [$title, $url])
                <li class="flex shrink-0 items-center gap-2 {{ $loop->last ? 'min-w-0 shrink' : '' }}">
                    @if ($url)
                        <a href="{{ $url }}" class="keep text-subtle underline-offset-2 hover:text-black hover:underline">{{ $title }}</a>
                        <span class="text-subtle/60" aria-hidden="true">›</span>
                    @else
                        <span class="block max-w-[46ch] truncate font-bold text-black max-sm:max-w-[28ch]" title="{{ $title }}" aria-current="page">{{ $title }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
</div>
