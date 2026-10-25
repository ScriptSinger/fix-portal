@extends('tw.layouts.app')
@php
    $isFiltered = request()->hasAny(['search', 'platform', 'extension', 'crc32', 'brand', 'series']);
    $isPaginated = request('page', 1) > 1;
    $toggleDir = $dir === 'asc' ? 'desc' : 'asc';
    $sortUrl = fn ($field) => request()->fullUrlWithQuery(['sort' => $field, 'dir' => $toggleDir, 'page' => null]);
    $mark = fn ($field) => $sort === $field ? ($dir === 'asc' ? ' ▲' : ' ▼') : '';
    $columns = ['id' => 'ID', 'title' => 'Название', 'brand' => 'Марка и модель', 'size' => 'Размер', 'date' => 'Дата', 'extension' => 'Расширение', 'platform' => 'Платформа', 'views_count' => 'Просмотры', 'downloads_count' => 'Загрузки'];
    $seriesAll = collect($seriesByBrand)->flatten()->unique()->sort()->values()->all();
@endphp
@section('title', 'Прошивки для бытовой техники | ' . config('app.name', 'Ufamasters'))
@section('description', 'Каталог прошивок для бытовой техники: поиск и просмотр файлов по моделям и платформам.')
@section('robots', ($isFiltered || $isPaginated) ? 'noindex, follow' : 'index, follow')
@section('wide', true)

@section('hero')
    <x-tw.page-header title="Прошивки для бытовой техники" :items="[['Главная', route('articles.index')], ['Прошивки', null]]" />
@endsection

@section('sidebar')
    @include('tw.partials.widgets.ad')
@endsection

@section('content')
    <div class="rounded-[5px] bg-white p-12 max-sm:px-4">
        @if ($catalogBrands->isNotEmpty())
            <p class="mb-6">
                <strong>Прошивки по маркам:</strong>
                @foreach ($catalogBrands as $b)
                    <a href="{{ route('firmwares.brand', ['brand' => $b['slug']]) }}" class="text-black hover:text-link">{{ $b['name'] }}</a>
                    ({{ $b['pages'] }}){{ ! $loop->last ? ' · ' : '' }}
                @endforeach
            </p>
        @endif

        <form method="GET" action="{{ route('firmwares.index') }}" class="mb-6"
            x-data="{ brand: @js(request('brand', '')), byBrand: @js($seriesByBrand), all: @js($seriesAll),
                      series: @js(mb_strtoupper((string) request('series'))),
                      get seriesList() { return this.brand ? (this.byBrand[this.brand] ?? []) : this.all; } }">
            <div class="grid gap-x-[30px] gap-y-4 md:grid-cols-4">
                <div class="md:col-span-2">
                    <label for="f-search" class="mb-2 block">Поиск</label>
                    <input id="f-search" type="search" name="search" value="{{ request('search') }}"
                        placeholder="Модель (например, WISL 105), серия или серийный номер"
                        class="block h-[38px] w-full border border-black/15 bg-white px-3 text-base text-[#464a4c] placeholder:text-[#a3a5a8] focus:border-brand-500 focus:outline-none">
                </div>
                @if ($brands->isNotEmpty())
                    <div>
                        <label for="f-brand" class="mb-2 block">Марка</label>
                        <select id="f-brand" name="brand" x-model="brand" @change="series = ''"
                            class="block h-[38px] w-full border border-[#ced4da] bg-white px-2 text-[15px] text-body focus:border-brand-500 focus:outline-none">
                            <option value="">Все</option>
                            @foreach ($brands as $brand => $pages)
                                <option value="{{ $brand }}">{{ $brand }} ({{ $pages }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="f-series" class="mb-2 block">Серия</label>
                        <select id="f-series" name="series" x-model="series"
                            class="block h-[38px] w-full border border-[#ced4da] bg-white px-2 text-[15px] text-body focus:border-brand-500 focus:outline-none">
                            <option value="">Все</option>
                            <template x-for="name in seriesList" :key="name"><option :value="name" x-text="name" :selected="name === series"></option></template>
                        </select>
                    </div>
                @endif
                <x-tw.select-plain name="platform" label="Платформа" :options="$platforms->all()" :value="request('platform')" />
                <x-tw.select-plain name="extension" label="Расширение" :options="$extensions->all()" :value="request('extension')" />
                <div>
                    <label for="f-crc32" class="mb-2 block">Хеш файла (MD5)</label>
                    <input id="f-crc32" type="text" name="crc32" value="{{ request('crc32') }}"
                        class="block h-[38px] w-full border border-black/15 bg-white px-3 text-base text-[#464a4c] focus:border-brand-500 focus:outline-none">
                </div>
                <x-tw.select-plain name="per_page" label="На странице" :options="[25, 50, 100, 200]" :value="$perPage" :blank="null" />
                <div class="flex items-end justify-center gap-3 md:col-span-4">
                    <button type="submit" class="bg-[#0275d8] px-8 py-[12.8px] text-sm font-bold leading-[17.5px] text-white hover:brightness-110">Применить</button>
                    <a href="{{ route('firmwares.index') }}" class="btn bg-[#0275d8] px-8 py-[12.8px] text-sm font-bold leading-[17.5px] text-white hover:brightness-110">Сбросить</a>
                </div>
            </div>
        </form>

        <div class="mb-4 overflow-x-auto">
            <table class="data-table text-[13px]">
                <thead>
                    <tr>
                        @foreach ($columns as $field => $label)
                            <th><a href="{{ $sortUrl($field) }}">{{ $label }}{{ $mark($field) }}</a></th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($firmwares as $firmware)
                        <tr>
                            <td>{{ $firmware->id }}</td>
                            <td><a href="{{ route('firmwares.show', ['firmware' => $firmware->id]) }}" title="{{ $firmware->title }}">{{ Str::limit($firmware->title, 40) }}</a></td>
                            <td>{{ $firmware->brand }} @if ($firmware->model)<span class="text-muted">{{ $firmware->model }}</span>@endif</td>
                            <td class="whitespace-nowrap">{{ $firmware->size >= 1024 ? number_format($firmware->size / 1024, 1, ',', ' ') . ' КБ' : $firmware->size . ' Б' }}</td>
                            <td class="whitespace-nowrap">{{ $firmware->date }}</td>
                            <td>{{ $firmware->extension }}</td>
                            <td>{{ $firmware->platform_norm ?: $firmware->platform }}</td>
                            <td>{{ $firmware->views_count }}</td>
                            <td>{{ $firmware->downloads_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $firmwares->onEachSide(1)->links('tw.partials.pagination') }}
    </div>
@endsection
