@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>
                            Комментарии
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Главная</a></li>
                            <li class="breadcrumb-item active">Комментарии</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                    class="fas fa-expand"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="dataTable" class="table table-bordered table-striped dataTable dtr-inline w-100"
                            data-locale={{ asset('assets/locale/datatable/russian.json') }}
                            data-routes='{
                                "index": "{{ route('api.comments.index') }}",
                                "edit": "{{ route('admin.comments.edit', ['comment' => ':id']) }}",
                                "userEdit": "{{ route('admin.users.edit', ['user' => ':id']) }}",
                                "destroy": "{{ route('api.comments.destroy', ['comment' => ':id']) }}",
                                "restore": "{{ route('api.comments.restore', ['comment' => ':id']) }}"
                            }'>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/admin/js/custom/datatables/comments.js') }}"></script>
@endpush
