@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Категория #{{ $category->id }}</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ url()->previous() }}">Назад</a></li>
                            <li class="breadcrumb-item active">Категория #{{ $category->id }}</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="col-md-6">
                <div class="card card-outline card-primary">
                    <div class="card-header">

                        <div class="card-tools">
                            <!-- This will cause the card to maximize when clicked -->
                            <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                    class="fas fa-expand"></i></button>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('categories.update', ['category' => $category->id]) }}"
                        id="quickForm" novalidate="novalidate">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="form-group">
                                <label for="InputText">Название</label>
                                <input type="text" name="title"
                                    class="form-control @error('title') is-invalid @enderror" id="InputTextCategory"
                                    value="{{ $category->title }}" aria-describedby="InputText-error" aria-invalid="true">
                                <span id="InputText-error" class="error invalid-feedback">
                                    @if ($errors->any())
                                        @foreach ($errors->all() as $error)
                                            {{ $error }}
                                        @endforeach
                                    @endif
                                </span>
                            </div>

                            <div class="form-group">
                                <label for="InputText">Slug</label>
                                <input type="text" class="form-control" id="InputTextCategory" disabled
                                    value="{{ $category->slug }}">

                            </div>

                            <div class="form-group">
                                <label for="InputText">Создано</label>
                                <input type="text" class="form-control" id="InputTextCategory" disabled
                                    value="{{ $category->dateAsCarbon->format('d-m-Y H:i:s') }}">
                            </div>
                        </div>

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
                    </form>

                    <form id="delete-form" class="d-none"
                        action="{{ route('categories.destroy', ['category' => $category->id]) }}" method="POST">
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
