@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Редактировать</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Категории</a></li>
                            <li class="breadcrumb-item active">Редактировать</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="col-md-6">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <span># {{ $category->id }}</span>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                    class="fas fa-expand"></i></button>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.categories.update', ['category' => $category->id]) }}">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="form-group">
                                <label>Название</label>
                                <input type="text" name="title"
                                    class="form-control @error('title') is-invalid @enderror"
                                    value="{{ $category->title }}">
                                <span class="error invalid-feedback">
                                    @if ($errors->any())
                                        @foreach ($errors->all() as $error)
                                            {{ $error }}
                                        @endforeach
                                    @endif
                                </span>
                            </div>
                            <div class="form-group">
                                <label>Slug</label>
                                <input type="text" class="form-control" disabled value="{{ $category->slug }}">
                            </div>
                            <div class="form-group">
                                <label>Создана</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="far fa-calendar-alt"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control float-right" id="reservation"
                                        value="{{ $category->dateAsCarbon->format('d-m-Y H:i:s') }}" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Изменена</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="far fa-calendar-alt"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control float-right" id="reservation"
                                        value="{{ $category->updated_at }}" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Удалена</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="far fa-calendar-alt"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control float-right" id="reservation"
                                        value="{{ $category->deleted_at }}" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-12">
                                    <a href="#" class="btn btn-danger"
                                        onclick="event.preventDefault(); document.getElementById('deleteForm').submit();">
                                        Удалить
                                    </a>
                                    <button type="submit" class="btn btn-primary float-right">Обновить</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <form id="deleteForm" class="d-none"
                        action="{{ route('admin.categories.destroy', ['category' => $category->id]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger" type="submit" onclick="return confirm('Подтвердите удаление')">
                            Удалить
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection
