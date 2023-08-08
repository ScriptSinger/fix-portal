@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Категории</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Blank Page</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="col-md-6 mx-auto">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Список категорий</h3>
                        <div class="card-tools">
                            <!-- This will cause the card to maximize when clicked -->
                            <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                    class="fas fa-expand"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('categories.create') }}" type="submit" class="btn btn-primary mb-3">Добавить
                            категорию</a>
                        @if (count($categories))
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th style="width: 10px">#</th>
                                        <th>Название</th>
                                        <th>Slug</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($categories as $category)
                                        <tr>
                                            <td>{{ $category->id }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <a type="button" class="btn btn-default"
                                                        href="{{ route('categories.show', [$category->id]) }}">
                                                        {{ $category->title }}</a>
                                                    <button type="button"
                                                        class="btn btn-default dropdown-toggle dropdown-icon"
                                                        data-toggle="dropdown" aria-expanded="false">
                                                        <span class="sr-only">Toggle Dropdown</span>
                                                    </button>
                                                    <div class="dropdown-menu" role="menu">
                                                        <a class="dropdown-item"
                                                            href="{{ route('categories.edit', ['category' => $category->id]) }}"><i
                                                                class="fas fa-edit"></i> Редактировать</a>

                                                        <div class="dropdown-divider"></div>
                                                        <form
                                                            action="{{ route('categories.destroy', ['category' => $category->id]) }}"
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
                                            <td>{{ $category->slug }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p>Категорий пока нет...</p>
                        @endif
                    </div>
                    <div class="card-footer clearfix">
                        <div class="pagination pagination-sm m-0 float-right">
                            {{ $categories->onEachSide(1)->links() }}</div>
                    </div>
                </div>

            </div>
        </section>
    </div>
@endsection
