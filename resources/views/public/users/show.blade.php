@extends('public.layouts.bar')
@section('title', "Прошивка: $user->title | " . config('app.name', 'Ufamasters'))
@section('page-title')
    <div class="page-title db">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                    <h2>Мастер: {{ $user->name }}</h2>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 hidden-xs-down hidden-sm-down">
                    <ol class="breadcrumb">
                        {{ Breadcrumbs::render('user', $user) }}
                    </ol>
                </div>
            </div>
        </div>
    </div>
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
        <div class="custombox authorbox clearfix">
            <h4 class="small-title">О мастере</h4>
            <div class="row">
                <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
                    <a href="#" role="button" data-toggle="modal" data-target="#avatarModal">
                        <img src="{{ optional($user->avatar)->uri ? Storage::url($user->avatar->uri) : asset('assets/front/images/avatar.png') }}"
                            class=" rounded-circle" width="64px" height="64px">
                    </a>
                </div>
                <div class="col-lg-10 col-md-10 col-sm-10 col-xs-12">
                    <small>{{ $user->location }}</small>
                    <h4><a href="#">{{ $user->name }}</a></h4>
                    <p>{{ $user->bio }}</p>
                    <div class="topsocial">
                        <a href="#" data-toggle="tooltip" data-placement="bottom" title=""
                            data-original-title="Телефон"><i class="fa fa-phone"> </i> {{ $user->phone }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('public.layouts.modal.avatar', ['user' => $user])
@endsection
