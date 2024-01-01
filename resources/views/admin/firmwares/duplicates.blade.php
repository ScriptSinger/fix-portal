@extends('admin.layouts.layout')
@section('search')
    <div class="navbar-search-block">
        {{-- <form class="form-inline" action="{{ route('admin.duplicates.search') }}" method="GET"> --}}

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
                        <h1>Дубликаты прошивок</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Главная</a></li>
                            <li class="breadcrumb-item active">Дубликаты прошивок</li>
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
                        @if ($duplicatePairs)
                            <form action="{{ route('admin.firmwares.remove_duplicates') }}" method="POST">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-primary mb-3"
                                    onclick="return confirm('Подтвердите удаление')">
                                    Удалить дубликаты
                                </button>
                            </form>
                            <table class="table table-bordered table-striped dataTable dtr-inline">
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
                                    @foreach ($duplicatePairs as $pair)
                                        <tr>
                                            <td>
                                                {{-- Первая запись --}}
                                                {{ $pair['first']['id'] }}


                                            <td>
                                                <a
                                                    href="{{ route('admin.firmwares.show', ['firmware' => $pair['first']['id']]) }}">
                                                    {{ $pair['first']['title'] }}</a>
                                            </td>


                                            <td>
                                                {{-- Первая запись --}}
                                                {{ $pair['first']['size'] }} КБ

                                            </td>
                                            <td>
                                                {{-- Первая запись --}}
                                                {{ $pair['first']['extension'] }}


                                            </td>
                                            <td>
                                                {{-- Первая запись --}}
                                                {{ $pair['first']['platform'] }}

                                            </td>
                                            <td>
                                                {{-- Первая запись --}}
                                                {{ $pair['first']['crc32'] }}

                                            </td>
                                        </tr>
                                        <tr>

                                            <td>

                                                {{-- Вторая запись --}}
                                                {{ $pair['second']['id'] }}
                                            </td>
                                            <td>

                                                {{-- Вторая запись --}}


                                                <a
                                                    href="{{ route('admin.firmwares.show', ['firmware' => $pair['first']['id']]) }}">
                                                    {{ $pair['first']['title'] }}</a>


                                            </td>
                                            <td>

                                                {{-- Вторая запись --}}
                                                {{ $pair['second']['size'] }} КБ
                                            </td>
                                            <td>

                                                {{-- Вторая запись --}}
                                                {{ $pair['second']['extension'] }}
                                            </td>
                                            <td>

                                                {{-- Вторая запись --}}
                                                {{ $pair['second']['platform'] }}
                                            </td>
                                            <td>

                                                {{-- Вторая запись --}}
                                                {{ $pair['second']['crc32'] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p>Дубликатов не найдено</p>
                        @endif
                    </div>
                    {{-- <div class="card-footer">{{ $duplicates->links('pagination::bootstrap-4') }}</div> --}}
                </div>
        </section>
    </div>
@endsection
