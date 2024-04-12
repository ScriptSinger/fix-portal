@extends('public.layouts.bar')
@section('title', "$firmware->title | " . config('app.name', 'Ufamasters'))
@section('page-title')
    <div class="page-title db">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                    <h2>{{ $firmware->title }}</h2>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 hidden-xs-down hidden-sm-down">

                    {{ Breadcrumbs::render('firmware', $firmware) }}

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
        <div class="blog-title-area">
            @include('public.layouts.widgets.sharing', ['reference' => $firmware->id])
        </div>
        <div class="custombox clearfix">
            <h4 class="small-title">Метаданные</h4>
            <div class="table-responsive">
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
            </div>

            <a class="btn btn-dark"
                href="{{ route('firmwares.download', ['filename' => $firmware->title . $firmware->extension]) }}">Скачать
                файл</a>
        </div>

        <hr class="invis1">

        <div class="custombox clearfix">

            <button class="btn btn-dark btn-link small-title" type="button" data-toggle="collapse"
                data-target="#collapsedTable" aria-expanded="false" aria-controls="collapsedTable">
                Параметры
            </button>

            <div class="collapse show" id="collapsedTable">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            {!! $firmware->data !!}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <hr class="invis1">

        @include('public.layouts.widgets.page.comments', [
            'instance' => $firmware,
        ])
    </div>
@endsection
