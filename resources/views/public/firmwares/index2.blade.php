@extends('public.layouts.bar')
@section('title', 'Каталог прошивок для бытовой техники | ' . config('app.name', 'Ufamasters'))
@section('page-title')
    <div class="page-title db">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                    <h2>Каталог прошивок для бытовой техники</h2>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 hidden-xs-down hidden-sm-down">
                    {{ Breadcrumbs::render('firmwares') }}
                </div>
            </div>
        </div>
    </div>
@endsection
@section('description',
    'Каталог прошивок для бытовой техники: холодильников, стиральных и посудомоечных машин, варочных
    панелей. Загруженные мастерами, теперь доступны на нашем сайте.')

@section('sidebar')
    <div class="sidebar">
        @include('public.layouts.widgets.sidebar.advertising')
    </div>
@endsection

@section('content')
    <div class="page-wrapper">

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Поиск</h3>
                <div class="card-tools">
                    <form class="input-group input-group-sm" method="GET" action="{{ route('firmwares.index') }}">
                        <input name="data" class="form-control float-right @error('title') has-error @enderror"
                            type="text" placeholder="Введите серийный номер или номер модели">
                        <div class="input-group-append">
                            <button role="button" class="btn btn-dark btn-block" type="submit">Найти</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap ">
                    @foreach ($firmwares as $firmware)
                        <tbody>
                            <tr>
                                <td><a
                                        href="{{ route('firmwares.show', ['firmware' => $firmware->id]) }}">{{ $firmware->title }}</a>
                                </td>
                            </tr>
                        </tbody>
                    @endforeach
                </table>
            </div>

        </div>

        <hr class="invis">

        <div class="row">
            <div class="container col-md-12">
                <div class="pagination justify-content-center pagination-sm">
                    {{ $firmwares->withQueryString()->onEachSide(0)->links('vendor.pagination.public') }}
                </div>
            </div>
        </div>
    </div>
@endsection
