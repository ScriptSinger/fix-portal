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
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            @if (isset($firmwaresCount))
                                {{ $firmwaresCount }}
                                {{ trans_choice('совпадение|совпадения|совпадений', $firmwaresCount, [], 'ru') }}
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
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Название</th>
                                        <th>Размер</th>
                                        <th>Расширение</th>
                                        <th>Платформа</th>
                                        <th>CRC32</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($firmwares as $firmware)
                                        <tr>
                                            <td>{{ $firmware->id }}</td>
                                            <td>
                                                <a
                                                    href="{{ route('admin.firmwares.show', ['firmware' => $firmware->id]) }}">
                                                    {{ $firmware->title }}</a>
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
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p>Совпадений не найдено</p>
                        @endif
                    </div>
                    <div class="card-footer">{{ $firmwares->links('pagination::bootstrap-4') }}</div>
                </div>
        </section>
    </div>
@endsection
