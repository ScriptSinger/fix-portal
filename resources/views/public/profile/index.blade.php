@extends('public.layouts.bar')
@section('title', 'Личный кабинет | ' . config('app.name', 'Ufamasters'))
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


@section('sidebar')

    <div class="sidebar">
        <div class="widget">
            <div class="mx-auto d-block text-center">
                <img src="{{ $user->avatar !== null ? asset('storage/' . $user->avatar) : asset('assets/front/images/avatar.png') }}"
                    alt="" class="img-fluid rounded-circle mb-3">
                <div>
                    <h2 class="widget-title mb-2">{{ $user->name }}</h2>
                    <p>{{ $user->location }}</p>
                </div>
            </div>
        </div>
        @include('public.layouts.includes.sidebar_widgets.advertising')
    </div>

@endsection

@section('content')
    <div class="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <form method="POST" enctype="multipart/form-data" action="{{ route('update-profile') }}"
                    class="form-wrapper">
                    @csrf
                    <h4>Личные данные</h4>

                    <div class="form-group">
                        <label for="name">Имя</label>
                        <input name="name" class="form-control @error('name') is-invalid  @enderror"
                            value="{{ $user->name }}" type="text" id="name">
                        <span class="form-control-feedback">
                            @error('name')
                                {{ $message }}
                            @enderror
                        </span>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input disabled type="text" class="form-control" placeholder="{{ $user->email }}"
                            id="email">
                    </div>

                    <div class="form-group">
                        <label for="phone">Телефон</label>
                        <input name="phone" type="text" class="form-control @error('phone') is-invalid @enderror"
                            value="{{ $user->phone }}" id="phone">
                        <span class="form-control-feedback">
                            @error('phone')
                                {{ $message }}
                            @enderror
                        </span>
                    </div>

                    <div class="form-group">
                        <label for="location">Город</label>
                        <input name="location" type="text" class="form-control @error('location') is-invalid @enderror"
                            placeholder="Местоположение" value="{{ $user->location }}" id="location">
                        <span class="form-control-feedback">
                            @error('location')
                                {{ $message }}
                            @enderror
                        </span>
                    </div>

                    <div class="form-group">
                        <label for="bio">Содержание</label>
                        <textarea name="bio" class="form-control" id="bio"></textarea>
                        <span class="form-control-feedback">
                            @error('bio')
                                {{ $message }}
                            @enderror
                        </span>
                    </div>

                    <div class="custom-file w-100 mb-4">
                        <label class="custom-file-control" for="avatar"> </label>
                        <input name="avatar" type="file" class="w-100" id="avatar">
                        <span class="form-control-feedback">
                            @error('avatar')
                                {{ $message }}
                            @enderror
                        </span>
                    </div>

                    <button role="button" type="submit" class="btn">Обновить</button>
                </form>
            </div>
        </div>
    </div><!-- end page-wrapper -->

@endsection
