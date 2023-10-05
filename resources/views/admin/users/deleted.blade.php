@extends('admin.layouts.layout')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Удаленные пользователи</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Главная</a></li>
                            <li class="breadcrumb-item active">Удаленные пользователи</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="col-md-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        {{-- <h3 class="card-title">Удаленные пользователи</h3> --}}
                        <div class="card-tools">
                            <!-- This will cause the card to maximize when clicked -->
                            <button type="button" class="btn btn-tool" data-card-widget="maximize"><i
                                    class="fas fa-expand"></i></button>
                        </div>
                    </div>
                    <div class="card-body">

                        @if ($deletedUsers->count() > 0)
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th style="width: 10px">#</th>
                                        <th>Имя</th>
                                        <th>Email</th>
                                        <th>Дата удаления</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($deletedUsers as $user)
                                        <tr>
                                            <td>{{ $user->id }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <a type="button" class="btn btn-default"
                                                        href="{{ route('users.show', [$user->id]) }}">
                                                        {{ $user->name }}</a>
                                                    <button type="button"
                                                        class="btn btn-default dropdown-toggle dropdown-icon"
                                                        data-toggle="dropdown" aria-expanded="false">
                                                        <span class="sr-only">Toggle Dropdown</span>
                                                    </button>
                                                    <div class="dropdown-menu" role="menu">
                                                        <a class="dropdown-item"
                                                            href="{{ route('deleted-users.restore', ['id' => $user->id]) }}"><i
                                                                class="fas fa-edit"></i> Восстановить</a>

                                                        <div class="dropdown-divider"></div>
                                                        <form action="{{ route('users.destroy', ['user' => $user->id]) }}"
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
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->getDeletedDate() }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p>Удаленных пользователей нет...</p>
                        @endif
                    </div>
                    <div class="card-footer clearfix">
                        <div class="pagination pagination-sm m-0 float-right">
                            {{ $deletedUsers->onEachSide(1)->links() }}</div>
                    </div>
                </div>

            </div>
        </section>
    </div>
@endsection
