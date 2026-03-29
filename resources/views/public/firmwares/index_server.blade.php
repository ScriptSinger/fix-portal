@extends('public.layouts.bar')
@section('title', 'Прошивки для бытовой техники | ' . config('app.name', 'Ufamasters'))
@section('description', 'Каталог прошивок для бытовой техники: поиск и просмотр файлов по моделям и платформам.')
@if (request()->has('page') && request('page') > 1)
    @section('robots', 'noindex, follow')
@endif
@section('full-width', true)
@section('sidebar-first', true)
@section('sidebar-col', 'col-lg-2 col-md-12 col-sm-12 col-xs-12 col-lg-push-10')
@section('content-col', 'col-lg-10 col-md-12 col-sm-12 col-xs-12 col-lg-pull-2')
@section('page-title')
    <div class="page-title db">
        <div class="page-title-hero-inner">
            <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                    <h2>Прошивки для бытовой техники</h2>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 hidden-xs-down hidden-sm-down">
                    <ol class="breadcrumb">
                        {{ Breadcrumbs::render('firmwares') }}
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('sidebar')
    <div class="sidebar">
        @include('public.layouts.widgets.sidebar.advertising')
    </div>
@endsection

@section('content')
    <div class="page-wrapper">
        <form class="mb-3 firmware-filter-form" method="GET" action="{{ route('firmwares.index') }}">
            <div class="row">
                <div class="col-md-6 col-sm-12 col-xs-12 mb-2">
                    <label>Поиск</label>
                    <input type="text" name="search" class="form-control"
                        placeholder="Модель или серийный номер"
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12 mb-2">
                    <label>Платформа</label>
                    <select id="filterPlatform" name="platform" class="form-control">
                        <option value="">Все</option>
                        @foreach ($platforms as $platform)
                            <option value="{{ $platform }}" @selected(request('platform') === $platform)>{{ $platform }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12 mb-2">
                    <label>Расширение</label>
                    <select id="filterExtension" name="extension" class="form-control">
                        <option value="">Все</option>
                        @foreach ($extensions as $extension)
                            <option value="{{ $extension }}" @selected(request('extension') === $extension)>{{ $extension }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12 mb-2">
                    <label>CRC32</label>
                    <input type="text" name="crc32" class="form-control" value="{{ request('crc32') }}">
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12 mb-2">
                    <label>На странице</label>
                    <select id="filterPerPage" name="per_page" class="form-control">
                        @foreach ([25, 50, 100, 200] as $size)
                            <option value="{{ $size }}" @selected(($perPage ?? 50) == $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-sm-12 col-xs-12 mb-2 filter-actions-row">
                    <label>&nbsp;</label>
                    <x-ui.filter-actions :reset-url="route('firmwares.index')" />
                </div>
            </div>
        </form>

        <div class="table-responsive" style="margin-bottom: 16px;">
            <table class="table table-bordered table-striped w-100">
                <thead>
                    @php
                        $toggleDir = $dir === 'asc' ? 'desc' : 'asc';
                        $sortUrl = function ($field) use ($toggleDir) {
                            return request()->fullUrlWithQuery(['sort' => $field, 'dir' => $toggleDir]);
                        };
                        $sortIndicator = function ($field) use ($sort, $dir) {
                            if ($sort !== $field) {
                                return '';
                            }
                            return $dir === 'asc' ? ' ▲' : ' ▼';
                        };
                    @endphp
                    <tr>
                        <th><a href="{{ $sortUrl('id') }}">ID{{ $sortIndicator('id') }}</a></th>
                        <th><a href="{{ $sortUrl('title') }}">Название{{ $sortIndicator('title') }}</a></th>
                        <th><a href="{{ $sortUrl('size') }}">Размер{{ $sortIndicator('size') }}</a></th>
                        <th><a href="{{ $sortUrl('date') }}">Дата{{ $sortIndicator('date') }}</a></th>
                        <th><a href="{{ $sortUrl('extension') }}">Расширение{{ $sortIndicator('extension') }}</a></th>
                        <th><a href="{{ $sortUrl('platform') }}">Платформа{{ $sortIndicator('platform') }}</a></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($firmwares as $firmware)
                        <tr>
                            <td>{{ $firmware->id }}</td>
                            <td>
                                <a href="{{ route('firmwares.show', ['firmware' => $firmware->id]) }}">
                                    <span title="{{ $firmware->title }}">{{ \Illuminate\Support\Str::limit($firmware->title, 40) }}</span>
                                </a>
                            </td>
                            <td>{{ $firmware->size }} КБ</td>
                            <td class="text-nowrap">{{ $firmware->date }}</td>
                            <td>{{ $firmware->extension }}</td>
                            <td>{{ $firmware->platform }}</td>
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
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/front/vendor/select2/select2.min.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('assets/front/vendor/select2/select2.min.js') }}"></script>
    <script>
        $(function() {
            $('#filterPlatform').select2({
                placeholder: 'Платформа',
                allowClear: true,
                width: '100%'
            });
            $('#filterExtension').select2({
                placeholder: 'Расширение',
                allowClear: true,
                width: '100%'
            });
            $('#filterPerPage').select2({
                minimumResultsForSearch: Infinity,
                width: '100%'
            });
        });
    </script>
@endpush
