@extends('public.layouts.bar')
@section('title', "$category->title | " . config('app.name', 'Ufamasters'))
@section('page-title')
    <div class="page-title db">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                    <h2> {{ $category->title }}</h2>
                </div><!-- end col -->
                <div class="col-lg-4 col-md-4 col-sm-12 hidden-xs-down hidden-sm-down">
                    <ol class="breadcrumb">
                        {{ Breadcrumbs::render('category', $category) }}
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
            @foreach ($posts as $post)
                <div class="blog-box wow fadeIn">
                    <div class="post-media">
                        <a href="{{ route('articles.show', ['article' => $post->slug]) }}" title="">
                            <img src="{{ $post->getImage('thumbnail') }}" alt="" class="img-fluid">
                            <div class="hovereffect">
                                <span></span>
                            </div>
                            <!-- end hover -->
                        </a>
                    </div>
                    <!-- end media -->
                    <div class="blog-meta big-meta text-center">
                        <div class="post-sharing">
                            <ul class="list-inline">
                                <li><a href="#" class="fb-button btn btn-primary"><i class="fa fa-facebook"></i> <span
                                            class="down-mobile">Share
                                            on Facebook</span></a></li>
                                <li><a href="#" class="tw-button btn btn-primary"><i class="fa fa-twitter"></i> <span
                                            class="down-mobile">Tweet
                                            on Twitter</span></a></li>
                                <li><a href="#" class="gp-button btn btn-primary"><i
                                            class="fa fa-google-plus"></i></a>
                                </li>
                            </ul>
                        </div><!-- end post-sharing -->
                        <h4><a href="{{ route('articles.show', ['article' => $post->slug]) }}"
                                title="">{{ $post->title }}</a></h4>
                        {!! $post->description !!}
                        <small><a href="{{ route('public.categories.show', ['category' => $category->slug]) }}"
                                title="">{{ $category->title }}</a></small>
                        <small>{{ $post->dateAsCarbon->diffForHumans() }}</small>
                        <small><a href="#" title="">by Jack</a></small>
                        <small><i class="fa fa-eye"></i> {{ $post->views }}</small>
                    </div><!-- end meta -->
                </div><!-- end blog-box -->

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
