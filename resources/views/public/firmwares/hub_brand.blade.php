@extends('public.layouts.bar')
@section('title', 'Прошивки ' . $brand['name'] . ': ' . $brand['pages'] . ' файлов, ' . $series->count() . ' серий | ' . config('app.name', 'Ufamasters'))
@section('description', $description)
@section('canonical', route('firmwares.brand', ['brand' => $brand['slug']]))
@section('robots', 'index, follow')
@section('full-width', true)
@section('sidebar-first', true)
@section('sidebar-col', 'col-lg-2 col-md-12 col-sm-12 col-xs-12 col-lg-push-10')
@section('content-col', 'col-lg-10 col-md-12 col-sm-12 col-xs-12 col-lg-pull-2')
@section('page-title')
    <div class="page-title db">
        <div class="page-title-hero-inner">
            <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                    <h1 style="font-size: 28px; margin: 0;">Прошивки {{ $brand['name'] }}</h1>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 hidden-xs-down hidden-sm-down">
                    <ol class="breadcrumb">
                        {{ Breadcrumbs::render('firmware.brand', $brand['name']) }}
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
        @endphp
        <div class="custombox clearfix">
            <p>
                Прошивки {{ $brand['name'] }} в каталоге UfaMasters: дампы модулей управления для ремонта и восстановления.
                Прошивок в каталоге: <strong>{{ number_format($brand['pages'], 0, ',', ' ') }}</strong>,
                серий моделей: <strong>{{ $series->count() }}</strong>.
                @if ($platformsText !== '')
                    Платформы модулей: {{ $platformsText }}.
                @endif
                @if ($extensionsText !== '')
                    Форматы файлов: {{ $extensionsText }}.
                @endif
                @if ($summary['group'])
                    Эти платформы относятся к модулям группы {{ $summary['group'] }}.
                @endif
            </p>
        </div>

        <div class="custombox clearfix">
            <h4 class="small-title">Серии моделей {{ $brand['name'] }}</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Серия</th>
                            <th>Прошивок</th>
                            <th>Моделей</th>
                            <th>Платформы</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($series as $row)
                            <tr>
                                <td>
                                    <a href="{{ route('firmwares.series', ['brand' => $brand['slug'], 'series' => $row['slug']]) }}">
                                        {{ $brand['name'] }} {{ $row['series'] }}
                                    </a>
                                </td>
                                <td>{{ $row['pages'] }}</td>
                                <td>{{ $row['models'] }}</td>
                                <td>{{ implode(', ', $row['platforms']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-muted small">
                Показаны серии, в которых не меньше {{ \App\Services\FirmwareCatalog::MIN_SERIES_PAGES }} файлов.
                Остальные прошивки {{ $brand['name'] }} можно найти
                <a href="{{ route('firmwares.index', ['brand' => $brand['name']]) }}">в общем каталоге с фильтрами</a>.
            </p>
        </div>

        @if ($otherBrands->isNotEmpty())
            <div class="custombox clearfix">
                <h4 class="small-title">Другие марки</h4>
                <p>
                    @foreach ($otherBrands as $other)
                        <a href="{{ route('firmwares.brand', ['brand' => $other['slug']]) }}">{{ $other['name'] }}</a>
                        ({{ $other['pages'] }}){{ ! $loop->last ? ' · ' : '' }}
                    @endforeach
                </p>
            </div>
        @endif

        <p class="text-muted small">
            Перед прошивкой сверьте код платы и версию с вашим модулем: файл не от той платы может вывести модуль из строя.
        </p>
    </div>
@endsection
