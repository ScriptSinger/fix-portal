@extends('public.layouts.app')
@section('title', 'Прошивки ' . $brand['name'] . ': ' . $brand['pages'] . ' файлов, ' . $series->count() . ' серий | ' . config('app.name', 'Ufamasters'))
@section('description', $description)
@section('canonical', route('firmwares.brand', ['brand' => $brand['slug']]))

@section('hero')
    <x-ui.page-header :title="'Прошивки ' . $brand['name']" :items="[['Статьи', route('articles.index')], ['Прошивки', route('firmwares.index')], [$brand['name'], null]]" />
@endsection

@section('sidebar-left')
    <x-ui.brand-nav :active="$brand['slug']" />
@endsection

@section('sidebar')
    @include('public.partials.sidebar', ['variant' => 'catalog'])
@endsection

@section('content')
    @php
        $platformsText = collect($summary['platforms'])->map(fn ($count, $name) => "{$name} ({$count})")->implode(', ');
        $extensionsText = collect($summary['extensions'])->map(fn ($count, $name) => "{$name} ({$count})")->implode(', ');
    @endphp
    <section class="bg-white p-5 sm:p-8">
        <p class="mb-0 text-[15px]">
            Прошивки {{ $brand['name'] }} в каталоге UfaMasters: дампы модулей управления для ремонта и восстановления.
            Прошивок в каталоге: <strong>{{ number_format($brand['pages'], 0, ',', ' ') }}</strong>,
            серий моделей: <strong>{{ $series->count() }}</strong>.
            @if ($platformsText !== ''){{ ' ' }}Платформы модулей: {{ $platformsText }}.@endif
            @if ($extensionsText !== ''){{ ' ' }}Форматы файлов: {{ $extensionsText }}.@endif
            @if ($summary['group']){{ ' ' }}Эти платформы относятся к модулям группы {{ $summary['group'] }}.@endif
        </p>
    </section>

    <section class="mt-6 bg-white" aria-labelledby="series-title">
        <h2 id="series-title" class="border-b border-line px-5 py-4 text-[13px] font-bold uppercase tracking-[1.5px] text-ink sm:px-8">Серии моделей {{ $brand['name'] }}</h2>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-[1px] text-ink">
                        <th class="border-b-2 border-black px-5 py-3 sm:pl-8">Серия</th>
                        <th class="border-b-2 border-black px-4 py-3">Прошивок</th>
                        <th class="border-b-2 border-black px-4 py-3 max-sm:hidden">Моделей</th>
                        <th class="border-b-2 border-black px-4 py-3 max-sm:hidden">Платформы</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($series as $row)
                        <tr class="hover:bg-brand-50">
                            <td class="border-b border-line px-5 py-3 sm:pl-8"><a href="{{ route('firmwares.series', ['brand' => $brand['slug'], 'series' => $row['slug']]) }}" class="font-bold text-black">{{ $brand['name'] }} {{ $row['series'] }}</a></td>
                            <td class="border-b border-line px-4 py-3">{{ $row['pages'] }}</td>
                            <td class="border-b border-line px-4 py-3 max-sm:hidden">{{ $row['models'] }}</td>
                            <td class="border-b border-line px-4 py-3 max-sm:hidden">{{ implode(', ', $row['platforms']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="mb-0 px-5 py-4 text-[13px] text-subtle sm:px-8">
            Показаны серии, в которых не меньше {{ \App\Services\FirmwareCatalog::MIN_SERIES_PAGES }} файлов.
            Остальные прошивки {{ $brand['name'] }} можно найти
            <a href="{{ route('firmwares.index', ['brand' => $brand['name']]) }}" class="text-black underline">в общем каталоге с фильтрами</a>.
        </p>
    </section>

    <div class="mt-6 border-l-4 border-brand-400 bg-brand-50 p-4 text-sm leading-[22px]">
        Перед прошивкой сверьте код платы и версию с вашим модулем: файл не от той платы может вывести модуль из строя.
    </div>
@endsection
