@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
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
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <a href="{{ route('questions.create') }}" type="submit" class="btn btn-primary mb-3">Добавить
                            вопрос</a>
                        <div class="card-tools">
                            <!-- This will cause the card to maximize when clicked -->
                            <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                    class="fas fa-expand"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="dataTable_wrapper" class="dataTables_wrapper dt-bootstrap4">


                            <table id="dataTable" class="table table-bordered table-striped dataTable dtr-inline w-100"
                                data-locale={{ asset('assets/locale/datatable/russian.json') }}>

                            </table>

                        </div>

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
                    url: '/api/heturion/questions',
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
                        data: 'views',
                        title: 'Просмотров'
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
                    {
                        data: 'updated_at',
                        title: 'Изменен',
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
                        return `<a href="categories/${row.id}/edit">${row.title}</a>`;


                    },

                }],

            });
        });
    </script>
@endsection
