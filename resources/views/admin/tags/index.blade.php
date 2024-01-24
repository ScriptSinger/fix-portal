@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Метки</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Главная</a></li>
                            <li class="breadcrumb-item active">Метки</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="col-md-12 mx-auto">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <a href="{{ route('tags.create') }}" type="submit" class="btn btn-primary">Добавить метку</a>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                    class="fas fa-expand"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="dataTable" class="table table-bordered table-striped dataTable dtr-inline w-100"
                            data-locale={{ asset('assets/locale/datatable/russian.json') }}>
                        </table>
                    </div>

                </div>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable({
                responsive: true,
                stateSave: true,
                select: true,
                language: {
                    url: $('#dataTable').data('locale')
                },

                ajax: {
                    url: '/api/heturion/tags',
                    dataSrc: ''
                },

                columns: [{
                        data: 'id',
                        title: 'ID'
                    },
                    {
                        data: 'title',
                        title: 'Title'
                    },
                    {
                        data: 'slug',
                        title: 'Slug'
                    },
                    {
                        data: 'created_at',
                        title: 'Создан',
                        render: function(data, type, row) {
                            var formattedDate = moment(data).format('DD.MM.YYYY HH:mm:ss');

                            return type === 'display' ? formattedDate : data;
                        }
                    },
                ],

                columnDefs: [{
                    targets: 1,
                    render: function(data, type, row, meta) {
                        return `<a href="tags/${row.id}/edit">${row.title}</a>`;


                    },

                }],

            });
        });
    </script>
@endsection
