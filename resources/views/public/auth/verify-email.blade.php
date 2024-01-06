@extends('public.layouts.bar')
@section('title', 'Подтверждение Email | ' . config('app.name', 'Ufamasters'))
@section('page-title')
    <div class="page-title db">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                    <h2>{{ __('Подтверждение Email') }}</h2>

                </div><!-- end col -->
                <div class="col-lg-4 col-md-4 col-sm-12 hidden-xs-down hidden-sm-down">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active">Главная</li>
                        <li class="breadcrumb-item active"> {{ __('Подтверждение Email') }}</li>
                    </ol>
                </div><!-- end col -->
            </div><!-- end row -->
        </div><!-- end container -->
    </div><!-- end page-title -->
@endsection

@section('sidebar')
    <div class="sidebar">
        @include('public.layouts.widgets.sidebar.prime_posts')
        @include('public.layouts.widgets.sidebar.advertising')
        @include('public.layouts.widgets.sidebar.prime_categories')
    </div>
@endsection

@section('content')
    <div class="page-wrapper">
        <div class="blog-custom-build">

            @if (session('status') == 'verification-link-sent')
                <div class="alert alert-success" role="alert">
                    {{ __('На ваш адрес электронной почты отправлена новая ссылка для подтверждения.') }}
                </div>
            @endif

            {{ __('Спасибо за регистрацию!  Прежде чем начать, пожалуйста, подтвердите свой адрес электронной почты, перейдя по ссылке, которую мы только что отправили вам на почту. Если вы не получили письмо, мы с удовольствием отправим вам еще одно.') }}

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf

                <button role="button" type="submit"
                    class="btn btn-link p-0 m-0 align-baseline">{{ __('Нажмите здесь') }}</button>
                {{ __('для отправки новой ссылки.') }}
            </form>

        </div>
    </div>
@endsection
