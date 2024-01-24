@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
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
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-outline card-primary">
                    <div class="card-header">

                        <div class="card-tools">
                            <!-- This will cause the card to maximize when clicked -->
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
                    url: '/api/heturion/comments',
                    dataSrc: ''
                },

                columns: [{
                        data: 'id',
                        title: 'ID'
                    },

                    {
                        data: 'user',
                        title: 'Пользователь',

                        render: function(data, type, row) {
                            return '<a href="/heturion/users/' + data.id + '/edit">' + data.name +
                                '</a>'
                        }

                    },

                    {
                        data: 'text',
                        title: 'Комментарий',
                        render: function(data, type, row) {
                            var truncatedText = (type === 'display' && data.length > 50) ? data
                                .substr(0, 50) + '...' : data;

                            return type === 'display' ?
                                '<a href="/heturion/comments/' + row.id + '/edit">' +
                                truncatedText +
                                '</a>' :
                                data;
                        }
                    },

                    {
                        data: 'created_at',
                        title: 'Создан',
                        render: function(data, type, row) {
                            // Форматирование даты с помощью moment.js
                            var formattedDate = moment(data).format('DD.MM.YYYY HH:mm:ss');

                            return type === 'display' ? formattedDate : data;
                        }
                    },

                ],
            });
        });
    </script>
@endsection
