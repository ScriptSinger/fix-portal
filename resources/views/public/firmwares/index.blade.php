@extends('public.layouts.bar')
@section('title', 'Прошивки для бытовой техники | ' . config('app.name', 'Ufamasters'))
@section('description', 'Каталог прошивок для бытовой техники: поиск и просмотр файлов по моделям и платформам.')
@if (request()->has('page') && request('page') > 1)
    @section('robots', 'noindex, follow')
@endif
@section('full-width', true)
@section('sidebar-col', 'col-lg-3 col-md-12 col-sm-12 col-xs-12')
@section('content-col', 'col-lg-9 col-md-12 col-sm-12 col-xs-12')
@section('page-title')
    <div class="page-title db">
        <div class="container">
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
        <style>
            .firmware-filters {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                margin-bottom: 12px;
            }
            .firmware-filters .filter-group {
                min-width: 200px;
                flex: 1 1 200px;
            }
            .firmware-filters .filter-group label {
                display: block;
                margin-bottom: 4px;
                font-weight: 600;
            }
            .select2-container--default .select2-selection--single {
                height: 38px;
                border: 1px solid #ced4da;
                border-radius: 4px;
            }
            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 36px;
            }
            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 36px;
            }
            .firmware-filters .btn.btn-primary:hover,
            .firmware-filters .btn.btn-primary:focus {
                filter: none !important;
                background-color: #007bff !important;
                border-color: #007bff !important;
            }
        </style>
        <form id="firmwareFilters" class="firmware-filters">
            <div class="filter-group">
                <label>Платформа</label>
                <select id="filterPlatform" name="platform" class="form-control">
                    <option value="">Все</option>
                    @foreach ($platforms as $platform)
                        <option value="{{ $platform }}">{{ $platform }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>Расширение</label>
                <select id="filterExtension" name="extension" class="form-control">
                    <option value="">Все</option>
                    @foreach ($extensions as $extension)
                        <option value="{{ $extension }}">{{ $extension }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>CRC32</label>
                <input type="text" name="crc32" class="form-control" placeholder="8a1b2c3d">
            </div>
            <div class="filter-group" style="flex: 0 0 160px;">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">Применить</button>
            </div>
            <div class="filter-group" style="flex: 0 0 160px;">
                <label>&nbsp;</label>
                <button type="button" id="resetFilters" class="btn btn-primary w-100">Сбросить</button>
            </div>
        </form>
        <style>
            .firmware-table-scroll {
                overflow-x: scroll;
                -webkit-overflow-scrolling: touch;
            }
            .firmware-table-scroll table {
                min-width: 1100px;
            }
            @media (max-width: 768px) {
                .firmware-table-scroll {
                    overflow-x: visible;
                }
                .firmware-table-scroll table {
                    min-width: 100%;
                }
            }
        </style>
        <div class="table-responsive container-fluid px-0 firmware-table-scroll">
            <table id="dataTable" class="table table-bordered table-striped dataTable dtr-inline w-100"
                data-locale={{ asset('assets/locale/datatable/russian.json') }}
                data-routes='{
                    "index": "{{ route('api.firmwares.index') }}",
                    "show": "{{ route('firmwares.show', ['firmware' => ':id']) }}"
                }'>
            </table>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/front/vendor/select2/select2.min.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('assets/front/js/custom/datatables/firmwares.js') }}"></script>
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
        });
    </script>
@endpush
