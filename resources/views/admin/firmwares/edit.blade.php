@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">

        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>{{ $firmware->title }}</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.firmwares.index') }}">Прошивки</a></li>
                            <li class="breadcrumb-item active">{{ $firmware->title }}</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <section class="content">
            <form method="POST" enctype="multipart/form-data"
                action="{{ route('admin.firmwares.update', ['firmware' => $firmware->id]) }}" id="quickForm"
                novalidate="novalidate">
                @csrf
                @method('PUT')
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-outline card-primary">
                                <div class="card-header">

                                    <div class="card-tools">

                                        <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                                class="fas fa-expand"></i></button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="InputText">Название</label>

                                        <input type="text" name="title"
                                            class="form-control @error('title') is-invalid @enderror" id="InputText"
                                            value="{{ $firmware->title }}">

                                        <span id="InputText-error" class="error invalid-feedback">

                                            @error('title')
                                                {{ $message }}
                                            @enderror
                                        </span>
                                    </div>

                                    <div class="form-group">
                                        <label for="InputSize">Размер</label>
                                        <input type="text" class="form-control" id="InputSize"
                                            value="{{ $firmware->size }} КБ" disabled>
                                    </div>

                                    <div class="form-group">
                                        <label for="InputDate">Дата</label>
                                        <input type="text" class="form-control" id="InputDate"
                                            value="{{ $firmware->date }}" disabled>
                                    </div>
                                    <div class="form-group">
                                        <label for="InputExtension">Расширение</label>
                                        <input type="text" class="form-control" id="InputExtension"
                                            value="{{ $firmware->extension }}" disabled>
                                    </div>

                                    <div class="form-group">
                                        <label for="InputPlatform">Платформа</label>
                                        <input type="text" class="form-control" id="InputPlatform"
                                            value="{{ $firmware->platform }}" disabled>
                                    </div>
                                    <div class="form-group">
                                        <label for="InputCRC32">CRC32</label>
                                        <input type="text" class="form-control" id="InputCRC32"
                                            value="{{ $firmware->crc32 }}" disabled>
                                    </div>

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

                                    <div class="card-tools">

                                        <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                                class="fas fa-expand"></i></button>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <table class="table table-bordered">
                                        {!! $firmware->data !!}
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </form>

            <div class="card">
                <div class="card-footer">
                    <div class="row">
                        <div class="col-12">
                            <a href="#" class="btn btn-danger"
                                onclick="event.preventDefault(); document.getElementById('delete-form').submit();">
                                Удалить
                            </a>
                            <button type="submit" class="btn btn-primary float-right">Редактировать</button>
                        </div>
                    </div>
                </div>
            </div>
            </form>

            <form id="delete-form" class="d-none"
                action="{{ route('admin.firmwares.destroy', ['firmware' => $firmware->id]) }}" method="POST">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger" type="submit" onclick="return confirm('Подтвердите удаление')">
                    Удалить
                </button>
            </form>
        </section>

    </div>
@endsection
