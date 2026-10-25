@extends('public.layouts.bar')
@php
    $pageNumber = (int) request('page', 1);
    $canonicalUrl = route('firmwares.series', ['brand' => $brand['slug'], 'series' => strtolower($seriesName)]);
@endphp
@section('title', 'Прошивки ' . $brand['name'] . ' ' . $seriesName . ': ' . $summary['pages'] . ' файлов' . ($pageNumber > 1 ? ', страница ' . $pageNumber : '') . ' | ' . config('app.name', 'Ufamasters'))
@section('description', $description)
@section('canonical', $canonicalUrl)
{{-- Вторая и следующие страницы списка только помогают обходу: в индекс идёт первая --}}
@section('robots', $pageNumber > 1 ? 'noindex, follow' : 'index, follow')
@section('full-width', true)
@section('sidebar-first', true)
@section('sidebar-col', 'col-lg-2 col-md-12 col-sm-12 col-xs-12 col-lg-push-10')
@section('content-col', 'col-lg-10 col-md-12 col-sm-12 col-xs-12 col-lg-pull-2')
@section('page-title')
    <div class="page-title db">
        <div class="page-title-hero-inner">
            <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                    <h1 style="font-size: 28px; margin: 0;">Прошивки {{ $brand['name'] }} {{ $seriesName }}</h1>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 hidden-xs-down hidden-sm-down">
                    <ol class="breadcrumb">
                        {{ Breadcrumbs::render('firmware.series', $brand['name'], $seriesName) }}
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('sidebar')
    <div class="sidebar">
        @include('public.layouts.widgets.sidebar.telegram_group')
        @include('public.layouts.widgets.sidebar.advertising')
    </div>
@endsection

@section('content')
    <div class="page-wrapper">
        @php
            $platformsText = collect($summary['platforms'])->map(fn ($count, $name) => "{$name} ({$count})")->implode(', ');
            $extensionsText = collect($summary['extensions'])->map(fn ($count, $name) => "{$name} ({$count})")->implode(', ');
            $years = $summary['year_from']
                ? ($summary['year_from'] === $summary['year_to'] ? $summary['year_from'] . ' год' : $summary['year_from'] . '–' . $summary['year_to'] . ' годы')
                : null;
        @endphp
        <div class="custombox clearfix">
            <p>
                Серия <strong>{{ $brand['name'] }} {{ $seriesName }}</strong>.
                Прошивок в каталоге: <strong>{{ number_format($summary['pages'], 0, ',', ' ') }}</strong>,
                моделей: <strong>{{ count($summary['models']) }}</strong>.
                @if ($platformsText !== '')
                    Платформы модулей: {{ $platformsText }}.
                @endif
                @if ($extensionsText !== '')
                    Форматы файлов: {{ $extensionsText }}.
                @endif
                @if ($years)
                    Файлы добавлялись в каталог в {{ $years }}.
                @endif
            </p>
        </div>

        @if ($summary['models'])
            <div class="custombox clearfix">
                <h4 class="small-title">Модели серии {{ $seriesName }}</h4>
                <p>
                    @foreach ($summary['models'] as $model => $count)
                        <a href="{{ route('firmwares.index', ['search' => $model, 'brand' => $brand['name']]) }}">{{ $model }}</a>
                        ({{ $count }}){{ ! $loop->last ? ' · ' : '' }}
                    @endforeach
                </p>
            </div>
        @endif

        <div class="custombox clearfix">
            <h4 class="small-title">Все прошивки серии</h4>
            <div class="table-responsive" style="margin-bottom: 16px;">
                <table class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Модель</th>
                            <th>Название файла</th>
                            <th>Платформа</th>
                            <th>Формат</th>
                            <th>Размер</th>
                            <th>Загрузки</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($firmwares as $firmware)
                            <tr>
                                <td class="text-nowrap">{{ $firmware->model }}</td>
                                <td>
                                    <a href="{{ route('firmwares.show', ['firmware' => $firmware->id]) }}">
                                        {{ \Illuminate\Support\Str::limit($firmware->title, 45) }}
                                    </a>
                                </td>
                                <td>{{ $firmware->platform_norm ?: $firmware->platform }}</td>
                                <td>{{ $firmware->extension }}</td>
                                <td class="text-nowrap">{{ $firmware->human_size }}</td>
                                <td>{{ $firmware->downloads_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row">
                <div class="container col-md-12">
                    <div class="row justify-content-center">
                        {{ $firmwares->onEachSide(1)->links('vendor.pagination.public') }}
                    </div>
                </div>
            </div>
        </div>

        <p>
            <a href="{{ route('firmwares.brand', ['brand' => $brand['slug']]) }}">Все серии {{ $brand['name'] }}</a> ·
            <a href="{{ route('firmwares.index') }}">Весь каталог прошивок</a>
        </p>
        <p class="text-muted small">
            Перед прошивкой сверьте код платы и версию с вашим модулем: файл не от той платы может вывести модуль из строя.
        </p>
    </div>
@endsection
