@extends('public.layouts.left_sidebar')
@section('title', 'Личный кабинет')
@section('page-title')
    <div class="page-title db">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                    <h2>Добро пожаловать, {{ $user->name }}<small class="hidden-xs-down hidden-sm-down">Ваш последний
                            визит:
                            12.11.2023 в 19:51
                        </small></h2>
                </div><!-- end col -->
                <div class="col-lg-4 col-md-4 col-sm-12 hidden-xs-down hidden-sm-down">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Главная</a></li>
                        <li class="breadcrumb-item active">Личный кабинет</li>
                    </ol>
                </div><!-- end col -->
            </div><!-- end row -->
        </div><!-- end container -->
    </div><!-- end page-title -->
@endsection
@section('content')
    <div class="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <form method="POST" enctype="multipart/form-data" action="{{ route('update-profile') }}"
                    class="form-wrapper has-danger">
                    @csrf
                    <h4>Личные данные</h4>
                    <span class="form-control-feedback">
                        @error('name')
                            {{ $message }}
                        @enderror
                    </span>
                    <input name="name" type="text" class="@error('name') is-invalid @enderror form-control"
                        value="{{ $user->name }}">

                    <input disabled type="text" class="form-control" placeholder="{{ $user->email }}">

                    <span class="form-control-feedback">
                        @error('phone')
                            {{ $message }}
                        @enderror
                    </span>
                    <input name="phone" type="text" class=" @error('phone') is-invalid @enderror form-control"
                        placeholder="Мобильный телефон" value="{{ $user->phone }}">
                    <span class="form-control-feedback">
                        @error('location')
                            {{ $message }}
                        @enderror
                    </span>
                    <input name="location" type="text" class=" @error('location') is-invalid @enderror form-control"
                        placeholder="Местоположение" value="{{ $user->location }}">
                    <span class="form-control-feedback">
                        @error('location')
                            {{ $message }}
                        @enderror
                    </span>
                    <textarea name="bio" class="form-control" placeholder="Биография">{{ $user->bio }}</textarea>

                    <label for="">Аватар

                    </label> @error('avatar')
                        {{ $message }}
                    @enderror
                    <input type="file" name="avatar" class="form-control  @error('avatar') is-invalid @enderror">
                    <span class="form-control-feedback">

                    </span>

                    <button role="button" type="submit" class="btn">Обновить</button>
                </form>
            </div>
        </div>
    </div><!-- end page-wrapper -->
    </div><!-- end col -->


@endsection
