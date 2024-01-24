@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>
                            <div class="user-block">
                                <img class="img-circle"
                                    src="{{ $comment->user->avatar !== null ? asset('storage/' . $comment->user->avatar) : asset('assets/front/images/avatar.png') }}"
                                    alt="User Image">
                                <span class="username"><a href="#">{{ $comment->user->name }} #
                                        {{ $comment->user->id }}</a></span>
                                <span class="description">Shared publicly - 7:30 PM Today</span>
                            </div>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.comments.index') }}">Комментарии </a></li>
                            <li class="breadcrumb-item active"> #{{ $comment->id }}</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="col-md-6">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        # {{ $comment->id }}
                        <div class="card-tools">
                            <!-- This will cause the card to maximize when clicked -->
                            <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                    class="fas fa-expand"></i></button>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('categories.update', ['category' => $comment->id]) }}"
                        id="quickForm" novalidate="novalidate">
                        @csrf
                        @method('PUT')
                        <div class="card-body">

                            <div class="form-group">
                                <label for="InputText">Содержание</label>
                                <textarea id="InputText" name="text" class="form-control " rows="7">{{ $comment->text }}</textarea>

                            </div>

                            <div class="form-group">
                                <label>Создан</label>
                                <input type="text" class="form-control" disabled value="{{ $comment->created_at }}">
                            </div>

                            <div class="form-group">
                                <label>Отредактирован</label>
                                <input type="text" class="form-control" disabled value="{{ $comment->updated_at }}">
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
                        action="{{ route('categories.destroy', ['category' => $comment->id]) }}" method="POST">
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
