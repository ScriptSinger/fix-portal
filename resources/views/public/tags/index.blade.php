@extends('public.layouts.bar')
@section('title', "Теги статей | " . config('app.name', 'Ufamasters'))
@section('page-title')
<div class="page-title db">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                <h2>Теги статей</h2>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-12 hidden-xs-down hidden-sm-down">
                <ol class="breadcrumb">
                    {{ Breadcrumbs::render('tags') }}
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
    <div class="blog-title-area">
        @isset($tags)
        <div class="tag-cloud-single">
            @foreach ($tags as $tag)
            <span><a href="{{ route('tags.show', ['tag' => $tag->slug]) }}"
                    title="">{{ $tag->title }}</a></span>
            @endforeach
        </div>
        @endisset
    </div>
</div>
@endsection