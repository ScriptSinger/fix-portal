@extends('public.layouts.app')
@section('title', 'Прошивки ' . $brand['name'] . ': ' . $brand['pages'] . ' файлов, ' . $series->count() . ' серий | ' . config('app.name', 'Ufamasters'))
@section('description', $description)
@section('canonical', route('firmwares.brand', ['brand' => $brand['slug']]))
@section('wide', true)

@section('hero')
    <x-ui.page-header :title="'Прошивки ' . $brand['name']" :items="[['Статьи', route('articles.index')], ['Прошивки', route('firmwares.index')], [$brand['name'], null]]" />
@endsection

@section('sidebar')
    @include('public.partials.widgets.telegram')
    @include('public.partials.widgets.ad')
@endsection

@section('content')
    @php
        $platformsText = collect($summary['platforms'])->map(fn ($count, $name) => "{$name} ({$count})")->implode(', ');
        $extensionsText = collect($summary['extensions'])->map(fn ($count, $name) => "{$name} ({$count})")->implode(', ');
    @endphp
    <div class="rounded-[5px] bg-white p-12 max-sm:px-4">
        <x-ui.box class="!px-8">
            <p class="mb-0">
                Прошивки {{ $brand['name'] }} в каталоге UfaMasters: дампы модулей управления для ремонта и восстановления.
                Прошивок в каталоге: <strong>{{ number_format($brand['pages'], 0, ',', ' ') }}</strong>,
                серий моделей: <strong>{{ $series->count() }}</strong>.
                @if ($platformsText !== ''){{ ' ' }}Платформы модулей: {{ $platformsText }}.@endif
                @if ($extensionsText !== ''){{ ' ' }}Форматы файлов: {{ $extensionsText }}.@endif
                @if ($summary['group']){{ ' ' }}Эти платформы относятся к модулям группы {{ $summary['group'] }}.@endif
            </p>
        </x-ui.box>

        <x-ui.box :title="'Серии моделей ' . $brand['name']">
            <div class="overflow-x-auto">
                <table class="data-table text-[13px]">
                    <thead><tr><th>Серия</th><th>Прошивок</th><th>Моделей</th><th>Платформы</th></tr></thead>
                    <tbody>
                        @foreach ($series as $row)
                            <tr>
                                <td><a href="{{ route('firmwares.series', ['brand' => $brand['slug'], 'series' => $row['slug']]) }}">{{ $brand['name'] }} {{ $row['series'] }}</a></td>
                                <td>{{ $row['pages'] }}</td>
                                <td>{{ $row['models'] }}</td>
                                <td>{{ implode(', ', $row['platforms']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mb-0 text-[13px] text-subtle">
                Показаны серии, в которых не меньше {{ \App\Services\FirmwareCatalog::MIN_SERIES_PAGES }} файлов.
                Остальные прошивки {{ $brand['name'] }} можно найти
                <a href="{{ route('firmwares.index', ['brand' => $brand['name']]) }}" class="text-black hover:text-link">в общем каталоге с фильтрами</a>.
            </p>
        </x-ui.box>

        @if ($otherBrands->isNotEmpty())
            <x-ui.box title="Другие марки">
                <p class="mb-0">
                    @foreach ($otherBrands as $other)
                        <a href="{{ route('firmwares.brand', ['brand' => $other['slug']]) }}" class="text-black hover:text-link">{{ $other['name'] }}</a>
                        ({{ $other['pages'] }}){{ ! $loop->last ? ' · ' : '' }}
                    @endforeach
                </p>
            </x-ui.box>
        @endif

        <p class="mb-0 text-[13px] text-subtle">Перед прошивкой сверьте код платы и версию с вашим модулем: файл не от той платы может вывести модуль из строя.</p>
    </div>
@endsection
