@extends('public.layouts.app')
@php
    $isFiltered = request()->hasAny(['search', 'platform', 'extension', 'crc32', 'brand', 'series']);
    $isPaginated = request('page', 1) > 1;
    $toggleDir = $dir === 'asc' ? 'desc' : 'asc';
    $sortUrl = fn ($field) => request()->fullUrlWithQuery(['sort' => $field, 'dir' => $toggleDir, 'page' => null]);
    $mark = fn ($field) => $sort === $field ? ($dir === 'asc' ? ' ▲' : ' ▼') : '';
    $columns = ['title' => 'Название', 'brand' => 'Марка и модель', 'platform' => 'Платформа', 'size' => 'Размер', 'downloads_count' => 'Загрузки'];
    $seriesAll = collect($seriesByBrand)->flatten()->unique()->sort()->values()->all();
    $sizeText = fn ($bytes) => $bytes >= 1024 ? number_format($bytes / 1024, 1, ',', ' ') . ' КБ' : $bytes . ' Б';
    // Выбранные фильтры: чипы со ссылкой «убрать»
    $labels = ['search' => 'Поиск', 'brand' => 'Марка', 'series' => 'Серия', 'platform' => 'Платформа', 'extension' => 'Расширение', 'crc32' => 'MD5'];
    $chips = collect($labels)->filter(fn ($label, $key) => request()->filled($key))
        ->map(fn ($label, $key) => ['label' => $label . ': ' . request($key), 'url' => request()->fullUrlWithoutQuery([$key, 'page'])]);
    $state = ['search' => (string) request('search'), 'brand' => (string) request('brand'), 'series' => mb_strtoupper((string) request('series')),
        'platform' => (string) request('platform'), 'extension' => (string) request('extension'), 'crc32' => (string) request('crc32')];
@endphp
@section('title', 'Прошивки для бытовой техники | ' . config('app.name', 'Ufamasters'))
@section('description', 'Каталог прошивок для бытовой техники: поиск и просмотр файлов по моделям и платформам.')
@section('robots', ($isFiltered || $isPaginated) ? 'noindex, follow' : 'index, follow')
@section('left-drawer', '1')

@section('hero')
    <x-ui.page-header title="Прошивки для бытовой техники" :items="[['Статьи', route('articles.index')], ['Прошивки', null]]" />
@endsection

@section('sidebar-left')
    <div x-data="firmwareFilter(@js(['url' => route('firmwares.index'), 'state' => $state, 'byBrand' => $seriesByBrand, 'allSeries' => $seriesAll]))"
        @keydown.escape.window="close()" class="contents">
        <button type="button" @click="show()" aria-label="Открыть фильтр"
            class="fixed left-0 top-1/2 z-[60] -translate-y-1/2 rotate-180 bg-brand-400 px-[9px] py-4 text-[13px] font-bold uppercase leading-none tracking-[1.5px] text-black shadow-[2px_0_8px_rgba(0,0,0,.35)] [writing-mode:vertical-rl] lg:hidden">Фильтр</button>
        <div x-show="open" x-cloak @click="close()" class="fixed inset-0 z-[70] bg-black/50 lg:hidden" aria-hidden="true"></div>

        <div class="fw-drawer lg:rounded-[5px] lg:bg-white" :class="{ open }">
            <form method="GET" action="{{ route('firmwares.index') }}" @submit.prevent="apply()" class="flex min-h-0 flex-1 flex-col">
                {{-- Шапка панели на телефоне --}}
                <div class="flex min-h-14 items-center gap-3 border-b border-line bg-surface px-4 lg:hidden">
                    <button type="button" x-show="group" x-cloak @click="group = null" class="py-2 text-lg font-bold" aria-label="Назад к списку групп">&larr;</button>
                    <span class="flex-1 text-lg font-bold text-ink" x-text="group ? ({ brand: 'Марка', series: 'Серия', platform: 'Платформа', extension: 'Расширение' }[group]) : 'Фильтр'"></span>
                    <button type="button" @click="close()" class="px-1 text-3xl leading-none" aria-label="Закрыть фильтр">&times;</button>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto p-4 lg:max-h-[calc(100vh-190px)] lg:p-5">
                    <h2 class="mb-4 hidden text-[13px] font-bold uppercase tracking-[1.5px] text-ink lg:block">Фильтр</h2>

                    <div x-show="!mobile || group === null" class="mb-4">
                        <label for="f-search" class="mb-1 block text-xs text-subtle">Поиск</label>
                        <input id="f-search" type="search" name="search" x-model="search" placeholder="Модель или серия" class="field">
                    </div>

                    @foreach (['brand', 'series', 'platform', 'extension'] as $key)
                        @php $title = ['brand' => 'Марка', 'series' => 'Серия', 'platform' => 'Платформа', 'extension' => 'Расширение'][$key]; @endphp
                        <section class="border-b border-line last:border-0 lg:mb-4 lg:border-0">
                            {{-- Телефон: строка группы с выбранным значением --}}
                            <button type="button" x-show="mobile && group === null" x-cloak @click="group = '{{ $key }}'"
                                class="grid w-full grid-cols-[1fr_auto] items-center gap-x-3 py-3 text-left">
                                <b class="text-[15px] text-ink">{{ $title }}</b>
                                <span aria-hidden="true" class="row-span-2 text-lg text-subtle">&rsaquo;</span>
                                <span class="text-[13px] text-body" x-text="summary('{{ $key }}')"></span>
                            </button>

                            {{-- Компьютер: марки списком, остальное выпадающими списками, чтобы панель не вытягивалась --}}
                            <div x-show="!mobile">
                                <h3 class="mb-1 text-xs text-subtle">{{ $title }}</h3>
                                @if ($key === 'brand')
                                    <ul>
                                        <li><label class="flex cursor-pointer items-center gap-3 py-1.5 text-sm"><input type="radio" name="d-brand" value="" x-model="brand" class="size-4 accent-black"> Все</label></li>
                                        @foreach ($brands as $name => $pages)
                                            <li><label class="flex cursor-pointer items-center gap-3 py-1.5 text-sm"><input type="radio" name="d-brand" value="{{ $name }}" x-model="brand" class="size-4 accent-black"><span class="flex-1">{{ $name }}</span><span class="text-subtle">{{ $pages }}</span></label></li>
                                        @endforeach
                                    </ul>
                                @else
                                    <select x-model="{{ $key }}" class="field" aria-label="{{ $title }}">
                                        <option value="">Все</option>
                                        @if ($key === 'series')
                                            <template x-for="name in seriesList" :key="name"><option :value="name" x-text="name"></option></template>
                                        @else
                                            @foreach ($key === 'platform' ? $platforms : $extensions as $name)<option value="{{ $name }}">{{ $name }}</option>@endforeach
                                        @endif
                                    </select>
                                @endif
                            </div>

                            {{-- Телефон: значения выбранной группы --}}
                            <ul x-show="mobile && group === '{{ $key }}'" x-cloak>
                                <li><label class="flex cursor-pointer items-center gap-3 py-2.5 text-[15px]"><input type="radio" name="m-{{ $key }}" value="" x-model="{{ $key }}" class="size-5 accent-black"> Все</label></li>
                                @if ($key === 'series')
                                    <template x-for="name in seriesList" :key="name"><li><label class="flex cursor-pointer items-center gap-3 py-2.5 text-[15px]"><input type="radio" name="m-{{ $key }}" :value="name" x-model="series" class="size-5 accent-black"><span x-text="name"></span></label></li></template>
                                @elseif ($key === 'brand')
                                    @foreach ($brands as $name => $pages)
                                        <li><label class="flex cursor-pointer items-center gap-3 py-2.5 text-[15px]"><input type="radio" name="m-{{ $key }}" value="{{ $name }}" x-model="brand" class="size-5 accent-black"><span class="flex-1">{{ $name }}</span><span class="text-subtle">{{ $pages }}</span></label></li>
                                    @endforeach
                                @else
                                    @foreach ($key === 'platform' ? $platforms : $extensions as $name)
                                        <li><label class="flex cursor-pointer items-center gap-3 py-2.5 text-[15px]"><input type="radio" name="m-{{ $key }}" value="{{ $name }}" x-model="{{ $key }}" class="size-5 accent-black"> {{ $name }}</label></li>
                                    @endforeach
                                @endif
                            </ul>
                        </section>
                    @endforeach

                    <div x-show="!mobile || group === null" class="mt-4">
                        <label for="f-crc32" class="mb-1 block text-xs text-subtle">Хеш файла (MD5)</label>
                        <input id="f-crc32" type="text" name="crc32" x-model="crc32" class="field">
                    </div>
                </div>

                <div class="flex flex-col gap-2 border-t border-line bg-white p-3 lg:border-0 lg:px-5 lg:pb-5 lg:pt-0">
                    <button type="submit"
                        class="h-12 w-full whitespace-nowrap bg-black px-3 text-sm font-bold text-brand-400">Применить</button>
                    <a href="{{ route('firmwares.index') }}" class="keep py-2 text-center text-[13px] font-bold text-ink underline">Сбросить фильтр</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('sidebar')
    @include('public.partials.sidebar', ['variant' => 'catalog'])
@endsection

@section('content')
    <div class="bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-4 py-4 sm:px-5">
            <span class="text-sm text-subtle">Найдено {{ number_format($firmwares->total(), 0, ',', ' ') }}</span>
            <form method="GET" action="{{ route('firmwares.index') }}" class="flex items-center gap-2 text-xs text-subtle">
                @foreach (request()->except(['per_page', 'page']) as $name => $value)
                    @if (is_scalar($value))<input type="hidden" name="{{ $name }}" value="{{ $value }}">@endif
                @endforeach
                <label for="f-per">На странице</label>
                <select id="f-per" name="per_page" onchange="this.form.submit()" class="field !h-9 !w-auto !py-0 !text-sm">
                    @foreach ([25, 50, 100, 200] as $n)<option value="{{ $n }}" @selected($perPage === $n)>{{ $n }}</option>@endforeach
                </select>
            </form>
            @if ($chips->isNotEmpty())
                <ul class="flex flex-wrap gap-2">
                    @foreach ($chips as $chip)
                        <li><a href="{{ $chip['url'] }}" class="keep inline-flex items-center gap-2 border border-black px-2.5 py-1 text-xs text-black hover:bg-brand-400" aria-label="Убрать фильтр: {{ $chip['label'] }}">{{ Str::limit($chip['label'], 36) }} <span aria-hidden="true">&times;</span></a></li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr>
                        @foreach ($columns as $field => $label)
                            <th class="border-b-2 border-black px-4 py-3 text-left text-xs font-bold uppercase tracking-[1px] text-ink @if ($field !== 'title') max-sm:hidden @else max-lg:pl-11 @endif">
                                <a href="{{ $sortUrl($field) }}" class="-my-3 inline-block py-3">{{ $label }}{{ $mark($field) }}</a></th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($firmwares as $firmware)
                        <tr class="hover:bg-brand-50">
                            <td class="border-b border-line px-4 py-3 align-top max-lg:pl-11">
                                <a href="{{ route('firmwares.show', ['firmware' => $firmware->id]) }}" title="{{ $firmware->title }}" class="font-bold text-black">{{ Str::limit($firmware->title, 44) }}</a>
                                <span class="mt-0.5 block text-xs text-subtle">ID {{ $firmware->id }} · {{ $firmware->date }} · {{ $firmware->extension }}</span>
                                <span class="mt-0.5 block text-xs text-body sm:hidden">{{ collect([trim($firmware->brand . ' ' . $firmware->model), $firmware->platform_norm ?: $firmware->platform, $sizeText($firmware->size)])->filter()->implode(' · ') }}</span>
                            </td>
                            <td class="border-b border-line px-4 py-3 align-top max-sm:hidden">{{ $firmware->brand }} @if ($firmware->model)<span class="text-subtle">{{ $firmware->model }}</span>@endif</td>
                            <td class="border-b border-line px-4 py-3 align-top max-sm:hidden">{{ $firmware->platform_norm ?: $firmware->platform }}</td>
                            <td class="whitespace-nowrap border-b border-line px-4 py-3 align-top max-sm:hidden">{{ $sizeText($firmware->size) }}</td>
                            <td class="border-b border-line px-4 py-3 align-top max-sm:hidden">{{ $firmware->downloads_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center">По этим фильтрам прошивок нет. <a href="{{ route('firmwares.index') }}" class="font-bold underline">Сбросить фильтр</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($catalogBrands->isNotEmpty())
            <p class="border-t border-line px-4 py-4 text-[13px] sm:px-5">
                <strong>Страницы марок:</strong>
                @foreach ($catalogBrands as $b)
                    <a href="{{ route('firmwares.brand', ['brand' => $b['slug']]) }}" class="text-black underline hover:text-link">{{ $b['name'] }}</a>
                    ({{ $b['pages'] }}){{ ! $loop->last ? ' · ' : '' }}
                @endforeach
            </p>
        @endif
        <div class="border-t border-line p-4">{{ $firmwares->onEachSide(1)->links('public.partials.pagination') }}</div>
    </div>
@endsection
