@extends('public.layouts.bar')
@section('title', "$category->title | " . config('app.name', 'Ufamasters'))
@section('description', $category->description ?? 'Категория статей: ' . $category->title)

@php
    $isPaginated = request('page', 1) > 1;
@endphp

@section('robots', $isPaginated ? 'noindex, follow' : 'index, follow')
@section('canonical', route('categories.show', $category->slug))


@section('page-title')
    <div class="page-title db">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                    <h2> {{ $category->title }}</h2>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 hidden-xs-down hidden-sm-down">
                    <ol class="breadcrumb">
                        {{ Breadcrumbs::render('category', $category) }}
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
        <div class="blog-custom-build">
            @foreach ($posts as $post)
                <div class="blog-box wow fadeIn">
                    <div class="post-media">
                        <a role="button" href="#" data-toggle="modal" data-target="#modal{{ $post->id }}">
                            @if ($post->thumbnail)
                                <img class="img-fluid" src="{{ $post->thumbnail->blog }}" alt="{{ $post->title }}"
                                    loading="lazy">
                            @else
                                <img src="{{ asset('/assets/front/upload/market_blog_01.jpg') }}" class="img-fluid"
                                    loading="lazy">
                            @endif
                            <div class="hovereffect">
                                <span></span>
                            </div>
                        </a>
                    </div>

                    @include('public.layouts.modal.index', [
                        'entity' => $post,
                        'image' => optional($post->thumbnail)->original,
                    ])

                    <div class="blog-meta big-meta text-center">
                        @include('public.layouts.widgets.sharing', [
                            'reference' => $post->slug,
                        ])

                        <h4><a href="{{ route('articles.show', ['article' => $post->slug]) }}"
                                title="">{{ $post->title }}</a></h4>
                        <p>{!! $post->description !!}</p>
                        <small>{{ $post->dateAsCarbon->diffForHumans() }}</small>
                        <small>{{ $post->administrator->name }}</small>
                        <small><i class="fa fa-eye"></i> {{ $post->views }}</small>
                    </div>
                </div>

                <hr class="invis">
            @endforeach
        </div>
    </div>

    <hr class="invis">
    <div class="row">
        <div class="container col-md-12">
            <div class="row justify-content-center">
                {{ $posts->onEachSide(1)->links('vendor.pagination.public') }}
            </div>
        </div>
    </div>

@endsection
