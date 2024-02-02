@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Прошивки</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Главная</a></li>
                            <li class="breadcrumb-item active">Прошивки</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-outline card-primary" style=" overflow-x: auto; max-width: 100%;">
                    <div class="card-header">
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                    class="fas fa-expand"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <table id="dataTable" class="table table-bordered table-striped dataTable dtr-inline w-100"
                                    data-locale={{ asset('assets/locale/datatable/russian.json') }}
                                    data-routes='{
                                        "index": "{{ route('api.firmwares.index') }}",
                                        "edit": "{{ route('admin.firmwares.edit', ['firmware' => ':id']) }}",
                                        "destroy": "{{ route('api.firmwares.destroy', ['firmware' => ':id']) }}",
                                        "restore": "{{ route('api.firmwares.restore', ['firmware' => ':id']) }}"
                                    }'>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/admin/js/custom/datatables/firmwares.js') }}"></script>
@endpush
