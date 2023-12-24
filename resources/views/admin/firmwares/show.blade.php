@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>{{ $firmware->title }}</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('firmwares.index') }}">Список прошивок</a></li>
                            <li class="breadcrumb-item active">{{ $firmware->title }}</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Метаданные</h3>
                                <div class="card-tools">
                                    <!-- This will cause the card to maximize when clicked -->
                                    <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                            class="fas fa-expand"></i></button>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">

                                    <tbody>
                                        <tr>
                                            <td>Размер</td>
                                            <td>{{ $firmware->size }}</td>
                                        </tr>

                                        <tr>
                                            <td>Дата</td>
                                            <td>{{ $firmware->date }}</td>
                                        </tr>
                                        <tr>
                                            <td>Расширение</td>
                                            <td>{{ $firmware->extension }}</td>
                                        </tr>
                                        <tr>
                                            <td>Платформа</td>
                                            <td>{{ $firmware->platform }}</td>
                                        </tr>
                                        <tr>
                                            <td>CRC32</td>
                                            <td>{{ $firmware->crc32 }}</td>
                                        </tr>
                                    </tbody>
                                </table>

                            </div>
                            <div class="card-footer">
                                <a class="btn btn-primary"
                                    href="{{ route('admin.download', ['filename' => $firmware->title . $firmware->extension]) }}">Скачать
                                    файл</a>
                            </div>
                        </div>

                    </div>

                    <div class="col-md-6">
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Результат анализа данных</h3>

                                <div class="card-tools">
                                    <!-- This will cause the card to maximize when clicked -->
                                    <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                            class="fas fa-expand"></i></button>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    {!! $firmware->data !!}
                                </table>
                            </div>
                            {{-- <div class="card-footer">
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
