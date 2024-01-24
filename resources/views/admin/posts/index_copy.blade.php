@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Список статей</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Главная</a></li>
                            <li class="breadcrumb-item active">Список статей</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Список статей</h3>
                        <div class="card-tools">
                            <!-- This will cause the card to maximize when clicked -->
                            <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                    class="fas fa-expand"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('posts.create') }}" type="submit" class="btn btn-primary mb-3">Добавить
                            статью</a>
                        @if (count($posts))
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th style="width: 10px">#</th>
                                        <th>Название</th>
                                        <th>Постоянная ссылка</th>
                                        <th>Категория</th>
                                        <th>Метки</th>
                                        <th>Дата</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($posts as $post)
                                        <tr>
                                            <td>{{ $post->id }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <a type="button" class="btn btn-default"
                                                        href="{{ route('posts.show', [$post->id]) }}">
                                                        {{ $post->title }}</a>
                                                    <button type="button"
                                                        class="btn btn-default dropdown-toggle dropdown-icon"
                                                        data-toggle="dropdown" aria-expanded="false">
                                                        <span class="sr-only">Toggle Dropdown</span>
                                                    </button>
                                                    <div class="dropdown-menu" role="menu">
                                                        <a class="dropdown-item"
                                                            href="{{ route('posts.edit', ['post' => $post->id]) }}"><i
                                                                class="fas fa-edit"></i> Редактировать</a>

                                                        <div class="dropdown-divider"></div>
                                                        <form action="{{ route('posts.destroy', ['post' => $post->id]) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="dropdown-item" type="submit" class=""
                                                                onclick="return confirm('Подтвердите удаление')">
                                                                <i class="fas fa-trash"></i> Удалить
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $post->slug }}</td>
                                            <td>{{ $post->category->title }}</td>
                                            <td>{{ $post->tags->pluck('title')->join(', ') }}</td>
                                            <td>{{ $post->created_at }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p>статей пока нет...</p>
                        @endif
                    </div>
                    <div class="card-footer clearfix">
                        <div class="pagination pagination-sm m-0 float-right">
                            {{ $posts->onEachSide(1)->links() }}</div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
