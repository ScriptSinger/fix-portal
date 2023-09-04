@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Редактировать статью</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ url()->previous() }}">Назад</a></li>
                            <li class="breadcrumb-item active">Редактировать статью</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <section class="content">
            <form method="POST" enctype="multipart/form-data" action="{{ route('posts.update', ['post' => $post->id]) }}"
                id="quickForm" novalidate="novalidate">
                @csrf
                @method('PUT')
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
                                            class="form-control @error('title') is-invalid @enderror" id="InputText"
                                            value="{{ $post->title }}" aria-describedby="InputText-error"
                                            aria-invalid="true" required>
                                        <span id="InputText-error" class="error invalid-feedback">
                                            @error('title')
                                                {{ $message }}
                                            @enderror
                                        </span>
                                    </div>
                                    <div class="form-group">
                                        <label for="InputLink">Постоянная ссылка</label>
                                        <input type="text" class="form-control" id="InputLink"
                                            value="{{ $post->slug }}" disabled>
                                    </div>

                                    <div class="form-group">
                                        <label for="InputTextArea1">Содержание</label>
                                        <textarea id="InputTextArea1" name="content" class="form-control @error('content') is-invalid @enderror" rows="7">{{ $post->content }}</textarea>
                                        <span id="InputTextArea1-error" class="error invalid-feedback">
                                            @error('content')
                                                {{ $message }}
                                            @enderror
                                        </span>
                                    </div>

                                    <div class="form-group">
                                        <label for="InputTextArea2">Мета-описание</label>
                                        <textarea id="InputTextArea2" name="description" class="form-control @error('description') is-invalid @enderror"
                                            rows="3">{{ $post->description }}</textarea>
                                        <span id="InputTextArea2-error" class="error invalid-feedback">
                                            @error('description')
                                                {{ $message }}
                                            @enderror
                                        </span>
                                    </div>

                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Сохранить</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card card-outline card-primary">
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
                                        <label for="InputSelect1"></label>
                                        <select class="select2 @error('category_id') is-invalid @enderror" id="InputSelect1"
                                            name="category_id" style="width: 100%;">
                                            @foreach ($categories as $key => $value)
                                                <option value="{{ $key }}"
                                                    @if ($key == $post->category_id) selected @endif>
                                                    {{ $value }}</option>
                                            @endforeach
                                        </select>
                                        <span id="InputSelect1-error" class="error invalid-feedback">
                                            @error('category_id')
                                                {{ $message }}
                                            @enderror
                                        </span>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <a href="{{ route('categories.create') }}" type="submit" class="btn btn-primary"><i
                                            class="fas fa-plus"></i> Добавить</a>
                                </div>
                            </div>
                            <div class="card card-outline card-primary">
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
                                        <label for="InputSelect2"></label>
                                        <select name="tags[]" id="InputSelect2" class="select2" multiple="multiple"
                                            data-placeholder="Выбрать тег" style="width: 100%;">
                                            @foreach ($tags as $key => $value)
                                                <option value="{{ $key }}"
                                                    @if (in_array($key, $post->tags->pluck('id')->all())) selected @endif>
                                                    {{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <a href="{{ route('tags.create') }}" type="submit" class="btn btn-primary"><i
                                            class="fas fa-plus"></i> Добавить</a>
                                </div>
                            </div>
                            <div class="card card-outline card-primary">
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
                                        <label for="InputFile"></label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" name="thumbnail" id="InputFile"
                                                    class="custom-file-input">
                                                <label class="custom-file-label" for="thumbnail">Choose file</label>
                                            </div>
                                        </div>
                                        <span id="InputFile-error" class="error invalid-feedback"
                                            style="display: inline;">
                                            @error('thumbnail')
                                                {{ $message }}
                                            @enderror
                                        </span>
                                    </div>
                                    <div><img class="img-thumbnail" src="{{ $post->getImage() }}" alt="">
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
