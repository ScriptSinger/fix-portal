@extends('public.layouts.bar')
@section('title', 'Категории статей | ' . config('app.name', 'Ufamasters'))
@section('description', 'Список категорий статей по ремонту бытовой техники.')
@section('page-title')
    <div class="page-title db">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                    <h2>Категории статей</h2>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 hidden-xs-down hidden-sm-down">
                    <ol class="breadcrumb">
                        {{ Breadcrumbs::render('categories') }}
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('sidebar')
    <div class="sidebar">
        @include('public.layouts.widgets.sidebar.telegram_group')
        @include('public.layouts.widgets.sidebar.advertising')
        @include('public.layouts.widgets.sidebar.prime_posts')
        @include('public.layouts.widgets.sidebar.prime_categories')
    </div>
@endsection

@section('content')
    <div class="page-wrapper">
        <div class="blog-title-area">
            @isset($categories)
                <div class="tag-cloud-single">
                    @foreach ($categories as $category)
                        <span><a href="{{ route('categories.show', ['category' => $category->slug]) }}"
                                title="">{{ $category->title }}</a></span>
                    @endforeach
                </div>
            @endisset
        </div>
    </div>
@endsection
