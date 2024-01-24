@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
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
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="col-md-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <a href="{{ route('users.create') }}" type="submit" class="btn btn-primary">Добавить
                        </a>
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
                    url: '/api/heturion/users',
                    dataSrc: ''
                },

                columns: [{
                        data: 'id',
                        title: 'ID'
                    },
                    {
                        data: 'name',
                        title: 'Имя'
                    },
                    {
                        data: 'email',
                        title: 'Email'
                    },

                    {
                        data: 'email_verified_at',
                        title: 'Верификация',
                        render: function(data, type, row) {
                            // Форматирование даты с помощью moment.js
                            var formattedDate = moment(data).format('DD.MM.YYYY HH:mm:ss');
                            return type === 'display' ? formattedDate : data;
                        }
                    },

                    {
                        data: 'created_at',
                        title: 'Регистрация',
                        render: function(data, type, row) {
                            // Форматирование даты с помощью moment.js
                            var formattedDate = moment(data).format('DD.MM.YYYY HH:mm:ss');

                            return type === 'display' ? formattedDate : data;
                        }
                    },

                    {
                        data: 'deleted_at',
                        title: 'Удаление',
                        render: function(data, type, row) {
                            // Форматирование даты с помощью moment.js
                            var formattedDate = moment(data).format('DD.MM.YYYY HH:mm:ss');

                            return type === 'display' ? formattedDate : data;
                        }
                    },




                ],

                columnDefs: [{
                    targets: 1,
                    render: function(data, type, row, meta) {
                        return `<a href="users/${row.id}/edit">${row.name}</a>`;

                    },

                }],

            });
        });
    </script>
@endsection
