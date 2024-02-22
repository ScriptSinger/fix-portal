@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Пользователи</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Главная</a></li>
                            <li class="breadcrumb-item active">Пользователи</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="col-md-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <a href="{{ route('admin.users.create') }}" type="submit" class="btn btn-primary">Создать
                        </a>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                    class="fas fa-expand"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="dataTable" class="table table-bordered table-striped dataTable dtr-inline w-100"
                            data-locale={{ asset('assets/locale/datatable/russian.json') }}
                            data-routes='{
                                "index": "{{ route('api.users.index') }}",
                                "edit": "{{ route('admin.users.edit', ['user' => ':id']) }}",
                                "destroy": "{{ route('api.users.destroy', ['user' => ':id']) }}",
                                "restore": "{{ route('api.users.restore', ['user' => ':id']) }}"
                            }'>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/admin/js/custom/datatables/users.js') }}"></script>
@endpush
