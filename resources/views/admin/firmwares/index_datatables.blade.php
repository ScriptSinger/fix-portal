@extends('admin.layouts.layout')
@section('search')
    <div class="navbar-search-block">
        <form class="form-inline" action="{{ route('admin.firmwares.search') }}" method="GET">

            <div class="input-group input-group-sm">
                <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search"
                    name="text">
                <div class="input-group-append">
                    <button class="btn btn-navbar" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Список прошивок</h1>
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







                        @if (count($firmwares))
                            <div id="example1_wrapper" class="dataTables_wrapper dt-bootstrap4">
                                <div class="row">
                                    <div class="col-sm-12">

                                        <table id="table" class="table table-bordered  dataTable dtr-inline"
                                            aria-describedby="example1_info">


                                            <thead>
                                                <form action="{{ route('admin.firmwares.index') }}" method="GET">
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">
                                                                <i class="fas fa-filter"></i>
                                                            </span>
                                                        </div>
                                                        <button type="submit"
                                                            class="form-control btn btn-default">Отправить</button>
                                                    </div>
                                                    <tr>
                                                        <th>
                                                            #
                                                        </th>
                                                        <th>
                                                            Название
                                                        </th>
                                                        <th>Размер</th>
                                                        <th>Расширение</th>
                                                        <th>Платформа</th>
                                                        <th>CRC32</th>
                                                        <th>
                                                            <div class="custom-control custom-checkbox">
                                                                <input name="is_duplicate" class="custom-control-input"
                                                                    type="checkbox" id="customCheckbox1" value=true>
                                                                <label for="customCheckbox1"
                                                                    class="custom-control-label">Дубликаты</label>
                                                            </div>

                                                        </th>

                                                    </tr>
                                                </form>
                                            </thead>

                                            <tbody>
                                                @foreach ($firmwares as $firmware)
                                                    <tr>
                                                        <td>{{ $firmware->id }}</td>
                                                        <td>

                                                            <div class="btn-group">
                                                                <a type="button" class="btn btn-default"
                                                                    href="{{ route('admin.firmwares.show', ['firmware' => $firmware->id]) }}">
                                                                    {{ $firmware->title }}</a>


                                                                <button type="button"
                                                                    class="btn btn-default dropdown-toggle dropdown-icon"
                                                                    data-toggle="dropdown" aria-expanded="false">
                                                                    <span class="sr-only">Toggle Dropdown</span>
                                                                </button>
                                                                <div class="dropdown-menu" role="menu">
                                                                    {{-- <a class="dropdown-item"
                                                                        href="{{ route('posts.edit', ['post' => $post->id]) }}"><i
                                                                            class="fas fa-edit"></i> Редактировать</a> --}}

                                                                    <div class="dropdown-divider"></div>
                                                                    <form
                                                                        action="{{ route('admin.firmwares.destroy', ['firmware' => $firmware->id]) }}"
                                                                        method="POST">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button class="dropdown-item" type="submit"
                                                                            class=""
                                                                            onclick="return confirm('Подтвердите удаление')">
                                                                            <i class="fas fa-trash"></i> Удалить
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>

                                                        </td>
                                                        <td>
                                                            {{ $firmware->size }} <span>КБ</span>
                                                        </td>
                                                        <td>
                                                            {{ $firmware->extension }}
                                                        </td>
                                                        <td>
                                                            {{ $firmware->platform }}
                                                        </td>
                                                        <td>
                                                            {{ $firmware->crc32 }}
                                                        </td>
                                                        <td>
                                                            {{ $firmware->is_duplicate }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Название</th>
                                                    <th>Размер</th>
                                                    <th>Расширение</th>
                                                    <th>Платформа</th>
                                                    <th>CRC32</th>
                                                    <th>Дубликат</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                                <div class="row">
                                    {{-- <div class="col-sm-12 col-md-7">
                                        <div class="dataTables_paginate paging_simple_numbers" id="example1_paginate">
                                            {{ $firmwares->withQueryString()->links('pagination::bootstrap-4') }}
                                        </div>
                                    </div> --}}
                                </div>
                            </div>
                        @else
                            <p>Совпадений не найдено</p>
                        @endif

                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {

            let table = $('#table').DataTable({
                "pageLength": 50, // Устанавливаем количество отображаемых записей на странице
                "lengthMenu": [50, 100, 200], // Опции выбора количества записей на странице
                // Другие опции и настройки DataTables

            });


        });
    </script>
@endsection
