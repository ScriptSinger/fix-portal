@extends('public.layouts.bar')
@section('title', 'Мастера по ремонту бытовой техники | ' . config('app.name', 'Ufamasters'))
@section('page-title')
    <div class="page-title db">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                    <h2>Мастера по ремонту бытовой техники</h2>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 hidden-xs-down hidden-sm-down">
                    {{ Breadcrumbs::render('users') }}
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
        <div class="table-responsive">
            <table id="dataTable" class="table table-bordered table-striped dataTable dtr-inline w-100"
                data-locale={{ asset('assets/locale/datatable/russian.json') }}
                data-routes='{
                    "index": "{{ route('api.masters.index') }}",
                    "show": "{{ route('users.show', ['user' => ':id']) }}"
                }'>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/front/js/custom/datatables/users.js') }}"></script>
@endpush
