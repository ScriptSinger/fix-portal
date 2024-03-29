@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Вопросы</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Главная</a></li>
                            <li class="breadcrumb-item active">Вопросы</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <a href="{{ route('admin.questions.create') }}" type="submit" class="btn btn-primary">Создать</a>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                    class="fas fa-expand"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="dataTable_wrapper" class="dataTables_wrapper dt-bootstrap4">
                            <table id="dataTable" class="table table-bordered table-striped dataTable dtr-inline w-100"
                                data-locale={{ asset('assets/locale/datatable/russian.json') }}
                                data-routes='{
                                    "index": "{{ route('api.questions.index') }}",
                                    "edit": "{{ route('admin.questions.edit', ['question' => ':id']) }}",
                                    "destroy": "{{ route('api.questions.destroy', ['question' => ':id']) }}",
                                    "restore": "{{ route('api.questions.restore', ['question' => ':id']) }}"
                                }'>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/admin/js/custom/datatables/questions.js') }}"></script>
@endpush
