@extends('admin.layouts.layout')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Прошивки</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Главная</a></li>
                            <li class="breadcrumb-item active">Список прошивок</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-outline card-primary" style=" overflow-x: auto; max-width: 100%;">
                    <div class="card-header">
                        <h3 class="card-title">
                            @if (isset($firmwaresCount))
                                {{ $firmwaresCount }}
                                {{ trans_choice('совпадение|совпадения|совпадений', $firmwaresCount, [], 'ru') }}
                            @else
                                <form method="POST" enctype="multipart/form-data"
                                    action="{{ route('admin.firmwares.markDuplicates') }}" id="quickForm"
                                    novalidate="novalidate">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-primary">Маркировать дубликаты</button>
                                </form>
                            @endif
                        </h3>
                        <div class="card-tools">
                            <!-- This will cause the card to maximize when clicked -->
                            <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                    class="fas fa-expand"></i></button>
                        </div>
                    </div>
                    <div class="card-body">

                        <div class="row">
                            <div class="col-sm-12">
                                <table id="dataTable" class="table table-bordered table-striped dataTable dtr-inline w-100"
                                    data-locale={{ asset('assets/locale/datatable/russian.json') }}>
                                </table>
                            </div>
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
                    url: '/api/heturion/firmwares',
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
                        data: 'size',
                        title: 'Размер'
                    },
                    {
                        data: 'date',
                        title: 'Дата',
                        className: 'text-nowrap'
                    },
                    {
                        data: 'extension',
                        title: 'Расширение'
                    },
                    {
                        data: 'platform',
                        title: 'Платформа'
                    },
                    {
                        data: 'data',
                        title: 'Платформа'
                    },
                    {
                        data: 'is_duplicate',
                        title: 'Дубликаты'
                    },

                ],

                columnDefs: [{
                        targets: 1,
                        render: function(data, type, row, meta) {
                            return `<a href="firmwares/${row.id}/edit">${row.title}</a>`;
                        }
                    },
                    {
                        targets: 6,
                        visible: false
                    }
                ]

            });
        });
    </script>
@endsection
