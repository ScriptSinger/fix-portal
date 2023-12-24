@extends('public.layouts.bar')
@section('title', 'Ufamasters - Ремонт Бытовой Техники :: Поиск')
@section('page-title')
    <div class="page-title db">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                    <h2>Прошивки для бытовой техники</h2>
                </div><!-- end col -->
                <div class="col-lg-4 col-md-4 col-sm-12 hidden-xs-down hidden-sm-down">
                    <ol class="breadcrumb">

                        <li class="breadcrumb-item active"><a href="">Главная</a></li>
                        <li class="breadcrumb-item active">Прошивки</li>
                    </ol>
                </div><!-- end col -->
            </div><!-- end row -->
        </div><!-- end container -->
    </div><!-- end page-title -->
@endsection

@section('sidebar')
    <div class="sidebar">
        <div class="widget-no-style">
            <div class="newsletter-widget text-center align-self-center">
                <h3>Поиск прошивок</h3>
                <p>Воспользуйтесь поиском прошивок. Введите ключевые слова</p>
                <form class="form-inline" method="GET" action="{{ route('firmwares.search') }}">
                    {{-- @csrf --}}
                    <input type="text" name="text" placeholder="Поиск прошивки.." required="" class="form-control">
                    <input role="button" type="submit" value="Найти" class="btn btn-default btn-block">
                </form>
            </div>
        </div>
        @include('public.layouts.includes.sidebar_widgets.advertising')
    </div>
@endsection

@section('content')
    <div class="page-wrapper" style=" overflow-x: auto; 
    max-width: 100%; ">
        @if (count($firmwares))
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Платформа</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($firmwares as $firmware)
                        <tr>
                            <td>
                                <a href="{{ route('firmwares.show', ['firmware' => $firmware->id]) }}">
                                    {{ $firmware->title }}</a>
                            </td>
                            <td>
                                {{ $firmware->platform }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Совпадений не найдено</p>
        @endif
    </div>
    <hr class="invis">
    <div class="row">
        <div class="container col-md-12">
            <div class="row justify-content-center">
                {{ $firmwares->onEachSide(0)->links('vendor.pagination.public') }}
            </div>
        </div>
    </div>
@endsection
