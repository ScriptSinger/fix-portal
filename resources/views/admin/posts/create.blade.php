@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Добавить статью</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ url()->previous() }}">Назад</a></li>
                            <li class="breadcrumb-item active">Добавить статью</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <form method="POST" enctype="multipart/form-data" action="{{ route('posts.store') }}" id="quickForm"
                novalidate="novalidate">
                @csrf
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-9">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <div class="card-tools">
                                        <!-- This will cause the card to maximize when clicked -->
                                        <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                                class="fas fa-expand"></i></button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="InputText">Название</label>
                                        <input type="text" name="title"
                                            class="form-control @error('title') is-invalid @enderror" id="InputTextTask"
                                            placeholder="Добавить заголовок" aria-describedby="InputText-error"
                                            aria-invalid="true" required>
                                        <span id="InputText-error" class="error invalid-feedback">
                                            @if ($errors->any())
                                                @foreach ($errors->all() as $error)
                                                    {{ $error }}
                                                @endforeach
                                            @endif
                                        </span>
                                    </div>
                                    <div class="form-group">
                                        <label for="content">Содержание</label>
                                        <textarea id="content" name="content" class="form-control @error('content') is-invalid @enderror" rows="7"
                                            placeholder="Enter ..."></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="description">Мета-описание</label>
                                        <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror"
                                            rows="3" placeholder="Enter ..."></textarea>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Сохранить</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card ">
                                <div class="card-header">
                                    <h3 class="card-title">Категории</h3>
                                    <div class="card-tools">
                                        <!-- This will cause the card to maximize when clicked -->
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="category_id"></label>
                                        <select class="form-control" id="category_id" name="category_id">
                                            @foreach ($categories as $key => $value)
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Метки</h3>
                                    <div class="card-tools">
                                        <!-- This will cause the card to maximize when clicked -->
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="tags"></label>
                                        <select name="tags[]" id="tags" class="select2" multiple="multiple"
                                            data-placeholder="Выбрать тег" style="width: 100%;">
                                            @foreach ($tags as $key => $value)
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="card ">
                                <div class="card-header">
                                    <h3 class="card-title">Изображение записи</h3>
                                    <div class="card-tools">
                                        <!-- This will cause the card to maximize when clicked -->
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="thumbnail"></label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" name="thumbnail" id="thumbnail"
                                                    class="custom-file-input">
                                                <label class="custom-file-label" for="thumbnail">Choose file</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </section>
    </div>
@endsection
