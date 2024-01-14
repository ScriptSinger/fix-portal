@extends('public.layouts.bar')
@section('title', "Прошивка: $firmware->title | " . config('app.name', 'Ufamasters'))
@section('page-title')
    <div class="page-title db">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                    <h2>Прошивка: {{ $firmware->title }}</h2>
                </div><!-- end col -->
                <div class="col-lg-4 col-md-4 col-sm-12 hidden-xs-down hidden-sm-down">
                    <ol class="breadcrumb">
                        {{ Breadcrumbs::render('firmware', $firmware) }}
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
    <div class="page-wrapper" style="overflow-x: auto; max-width: 100%;">
        <div class="custombox clearfix">
            <h4 class="small-title">Метаданные</h4>
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <td>Размер</td>
                        <td>{{ $firmware->size }} КБ</td>
                    </tr>
                    <tr>
                        <td>Дата</td>
                        <td>{{ $firmware->date }}</td>
                    </tr>
                    <tr>
                        <td>Расширение</td>
                        <td>{{ $firmware->extension }}</td>
                    </tr>
                    <tr>
                        <td>Платформа</td>
                        <td>{{ $firmware->platform }}</td>
                    </tr>
                    <tr>
                        <td>CRC32</td>
                        <td>{{ $firmware->crc32 }}</td>
                    </tr>
                </tbody>
            </table>

            <a class="btn btn-primary"
                href="{{ route('firmwares.download', ['filename' => $firmware->title . $firmware->extension]) }}">Скачать
                файл</a>
        </div>

        <hr class="invis1">

        <div class="custombox clearfix">
            <h4 class="btn btn-link small-title" role="button" data-toggle="collapse" data-target="#collapsedTable">
                Параметры</h4>
            <div class="collapse" id="collapsedTable">
                <table class="table table-bordered">
                    <tbody>
                        {!! $firmware->data !!}
                    </tbody>
                </table>
            </div>
        </div>

        <hr class="invis1">

        @include('public.partials.comments', [
            'instance' => $firmware,
        ])

    </div>

@endsection
