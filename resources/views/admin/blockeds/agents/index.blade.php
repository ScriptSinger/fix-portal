@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>User-agents</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Главная</a></li>
                            <li class="breadcrumb-item active">User-agents</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <a href="{{ route('admin.agents.create') }}" type="submit" class="btn btn-primary">Добавить</a>
                        <button class="btn btn-danger" type="submit"
                            onclick="event.preventDefault(); if(confirm('Вы уверены, что хотите очистить?')) {document.getElementById('clear').submit();}">
                            Очистить
                        </button>

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
                                    "index": "{{ route('api.agents.index') }}",
                                    "edit": "{{ route('admin.agents.edit', ['agent' => ':id']) }}",
                                    "destroy": "{{ route('api.agents.destroy', ['agent' => ':id']) }}",
                                    "restore": "{{ route('api.agents.restore', ['agent' => ':id']) }}"
                                }'>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

            <form id="clear" class="d-none" action="{{ route('admin.agents.clear') }}" method="POST">
                @csrf
                @method('DELETE')
                <input type="submit">
            </form>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/admin/js/custom/datatables/agents.js') }}"></script>
@endpush
