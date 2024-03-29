@extends('public.layouts.bar')
@section('title', 'Прошивки для бытовой техники | ' . config('app.name', 'Ufamasters'))
@section('page-title')
    <div class="page-title db">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                    <h2>Прошивки для бытовой техники</h2>
                </div><!-- end col -->
                <div class="col-lg-4 col-md-4 col-sm-12 hidden-xs-down hidden-sm-down">
                    <ol class="breadcrumb">
                        {{ Breadcrumbs::render('firmwares') }}
                    </ol>
                </div><!-- end col -->
            </div><!-- end row -->
        </div><!-- end container -->
    </div><!-- end page-title -->
@endsection

@section('sidebar')
    <div class="sidebar">
        @include('public.layouts.widgets.sidebar.advertising')
    </div>
@endsection

@section('content')
    <div class="page-wrapper">
        <div class="table-responsive">
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

@push('scripts')
    <script src="{{ asset('assets/front/js/custom/datatables/firmwares.js') }}"></script>
@endpush
