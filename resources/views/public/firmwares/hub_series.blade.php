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

@section('hero')
    <x-ui.page-header :title="'Прошивки ' . $brand['name'] . ' ' . $seriesName" :items="[ ['Прошивки', route('firmwares.index')],
        [$brand['name'], route('firmwares.brand', ['brand' => $brand['slug']])], [$seriesName, null]]" />
@endsection

@section('sidebar-left')
    <x-ui.brand-nav :active="$brand['slug']" />
@endsection

@section('sidebar')
    @include('public.partials.sidebar', ['variant' => 'catalog'])
@endsection

@section('content')
    <section class="bg-white p-5 sm:p-8">
        <p class="mb-0 text-[15px]">
            Серия <strong>{{ $brand['name'] }} {{ $seriesName }}</strong>.
            Прошивок в каталоге: <strong>{{ number_format($summary['pages'], 0, ',', ' ') }}</strong>, моделей: <strong>{{ count($summary['models']) }}</strong>.
            @if ($platformsText !== ''){{ ' ' }}Платформы модулей: {{ $platformsText }}.@endif
            @if ($extensionsText !== ''){{ ' ' }}Форматы файлов: {{ $extensionsText }}.@endif
            @if ($years){{ ' ' }}Файлы добавлялись в каталог в {{ $years }}.@endif
        </p>
        @if ($summary['models'])
            <h2 class="mb-2 mt-5 text-[13px] font-bold uppercase tracking-[1.5px] text-ink">Модели серии {{ $seriesName }}</h2>
            <ul class="flex flex-wrap gap-2">
                @foreach ($summary['models'] as $model => $count)
                    <li><a href="{{ route('firmwares.index', ['search' => $model, 'brand' => $brand['name']]) }}"
                            class="keep inline-flex items-center gap-1.5 border border-black px-2.5 py-1 text-xs text-black hover:bg-brand-400">{{ $model }} <span class="text-subtle">{{ $count }}</span></a></li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="mt-6 bg-white" aria-labelledby="all-title">
        <h2 id="all-title" class="border-b border-line px-5 py-4 text-[13px] font-bold uppercase tracking-[1.5px] text-ink sm:px-8">Все прошивки серии</h2>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-[1px] text-ink">
                        <th class="border-b-2 border-black px-5 py-3 sm:pl-8">Файл</th>
                        <th class="border-b-2 border-black px-4 py-3 max-sm:hidden">Модель</th>
                        <th class="border-b-2 border-black px-4 py-3 max-sm:hidden">Платформа</th>
                        <th class="border-b-2 border-black px-4 py-3 max-sm:hidden">Размер</th>
                        <th class="border-b-2 border-black px-4 py-3 max-sm:hidden">Загрузки</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($firmwares as $firmware)
                        <tr class="hover:bg-brand-50">
                            <td class="border-b border-line px-5 py-3 align-top sm:pl-8">
                                <a href="{{ route('firmwares.show', ['firmware' => $firmware->id]) }}" class="font-bold text-black">{{ Str::limit($firmware->title, 45) }}</a>
                                <span class="mt-0.5 block text-xs text-subtle">.{{ ltrim((string) $firmware->extension, '.') }}</span>
                                <span class="mt-0.5 block text-xs text-body sm:hidden">{{ collect([$firmware->model, $firmware->platform_norm ?: $firmware->platform, $firmware->human_size])->filter()->implode(' · ') }}</span>
                            </td>
                            <td class="whitespace-nowrap border-b border-line px-4 py-3 align-top max-sm:hidden">{{ $firmware->model }}</td>
                            <td class="border-b border-line px-4 py-3 align-top max-sm:hidden">{{ $firmware->platform_norm ?: $firmware->platform }}</td>
                            <td class="whitespace-nowrap border-b border-line px-4 py-3 align-top max-sm:hidden">{{ $firmware->human_size }}</td>
                            <td class="border-b border-line px-4 py-3 align-top max-sm:hidden">{{ $firmware->downloads_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-line p-4">{{ $firmwares->onEachSide(1)->links('public.partials.pagination') }}</div>
    </section>

    <p class="mt-6 text-sm">
        <a href="{{ route('firmwares.brand', ['brand' => $brand['slug']]) }}" class="text-black underline">Все серии {{ $brand['name'] }}</a> ·
        <a href="{{ route('firmwares.index') }}" class="text-black underline">Весь каталог прошивок</a>
    </p>
    <div class="border-l-4 border-brand-400 bg-brand-50 p-4 text-sm leading-[22px]">
        Перед прошивкой сверьте код платы и версию с вашим модулем: файл не от той платы может вывести модуль из строя.
    </div>
@endsection
