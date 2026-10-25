@extends('public.layouts.app')
@php
    $pageNumber = (int) request('page', 1);
    $years = $summary['year_from']
        ? ($summary['year_from'] === $summary['year_to'] ? $summary['year_from'] . ' год' : $summary['year_from'] . '–' . $summary['year_to'] . ' годы')
        : null;
    $platformsText = collect($summary['platforms'])->map(fn ($count, $name) => "{$name} ({$count})")->implode(', ');
    $extensionsText = collect($summary['extensions'])->map(fn ($count, $name) => "{$name} ({$count})")->implode(', ');
@endphp
@section('title', 'Прошивки ' . $brand['name'] . ' ' . $seriesName . ': ' . $summary['pages'] . ' файлов' . ($pageNumber > 1 ? ', страница ' . $pageNumber : '') . ' | ' . config('app.name', 'Ufamasters'))
@section('description', $description)
@section('canonical', route('firmwares.series', ['brand' => $brand['slug'], 'series' => strtolower($seriesName)]))
@section('robots', $pageNumber > 1 ? 'noindex, follow' : 'index, follow')
@section('wide', true)

@section('hero')
    <x-ui.page-header :title="'Прошивки ' . $brand['name'] . ' ' . $seriesName" :items="[
        ['Главная', route('articles.index')], ['Прошивки', route('firmwares.index')],
        [$brand['name'], route('firmwares.brand', ['brand' => $brand['slug']])], [$seriesName, null]]" />
@endsection

@section('sidebar')
    @include('public.partials.widgets.telegram')
    @include('public.partials.widgets.ad')
@endsection

@section('content')
    <div class="rounded-[5px] bg-white p-12 max-sm:px-4">
        <x-ui.box>
            <p class="mb-0">
                Серия <strong>{{ $brand['name'] }} {{ $seriesName }}</strong>.
                Прошивок в каталоге: <strong>{{ number_format($summary['pages'], 0, ',', ' ') }}</strong>, моделей: <strong>{{ count($summary['models']) }}</strong>.
                @if ($platformsText !== ''){{ ' ' }}Платформы модулей: {{ $platformsText }}.@endif
                @if ($extensionsText !== ''){{ ' ' }}Форматы файлов: {{ $extensionsText }}.@endif
                @if ($years){{ ' ' }}Файлы добавлялись в каталог в {{ $years }}.@endif
            </p>
        </x-ui.box>

        @if ($summary['models'])
            <x-ui.box :title="'Модели серии ' . $seriesName">
                <p class="mb-0">
                    @foreach ($summary['models'] as $model => $count)
                        <a href="{{ route('firmwares.index', ['search' => $model, 'brand' => $brand['name']]) }}" class="text-black hover:text-link">{{ $model }}</a>
                        ({{ $count }}){{ ! $loop->last ? ' · ' : '' }}
                    @endforeach
                </p>
            </x-ui.box>
        @endif

        <x-ui.box title="Все прошивки серии">
            <div class="mb-4 overflow-x-auto">
                <table class="data-table text-[13px]">
                    <thead><tr><th>Модель</th><th>Название файла</th><th>Платформа</th><th>Формат</th><th>Размер</th><th>Загрузки</th></tr></thead>
                    <tbody>
                        @foreach ($firmwares as $firmware)
                            <tr>
                                <td class="whitespace-nowrap">{{ $firmware->model }}</td>
                                <td><a href="{{ route('firmwares.show', ['firmware' => $firmware->id]) }}">{{ Str::limit($firmware->title, 45) }}</a></td>
                                <td>{{ $firmware->platform_norm ?: $firmware->platform }}</td>
                                <td>{{ $firmware->extension }}</td>
                                <td class="whitespace-nowrap">{{ $firmware->human_size }}</td>
                                <td>{{ $firmware->downloads_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $firmwares->onEachSide(1)->links('public.partials.pagination') }}
        </x-ui.box>

        <p>
            <a href="{{ route('firmwares.brand', ['brand' => $brand['slug']]) }}" class="text-black hover:text-link">Все серии {{ $brand['name'] }}</a> ·
            <a href="{{ route('firmwares.index') }}" class="text-black hover:text-link">Весь каталог прошивок</a>
        </p>
        <p class="mb-0 text-[13px] text-muted">Перед прошивкой сверьте код платы и версию с вашим модулем: файл не от той платы может вывести модуль из строя.</p>
    </div>
@endsection
