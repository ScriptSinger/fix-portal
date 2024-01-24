@extends('public.layouts.banner')
@section('title', 'Статьи | ' . config('app.name', 'Ufamasters'))
@section('banner')

    <section
        style="background-image: url('{{ optional($customization)->getImage('banner') ?? asset('assets/front/images/power_unit.jpg') }}');"
        id="cta" class="section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-12 align-self-center">
                    <h2>{{ optional($customization)->title ?? 'Ремонт бытовой техники' }} </h2>

                    <p class="lead">
                        {{ optional($customization)->description ?? 'Узнайте как решить проблемы с бытовой техникой от опытных пользователей. Регистрируйтесь для создания своего вопроса.' }}
                    </p>
                    </p>
                    <a href="{{ route('questions.create') }}" class="btn btn-primary">Создать вопрос</a>
                </div>
                <div class="col-lg-4 col-md-12">
                    @include('public.layouts.widgets.banner.posts_search')
                </div>
            </div>
        </div>
    </section>
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
            @if (count($posts))
                @foreach ($posts as $post)
                    <div class="blog-box wow fadeIn">
                        <div class="post-media">
                            <a href="{{ route('articles.index', ['article' => $post->slug]) }}" title="">

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
                                    <li><a href="#" class="fb-button btn btn-primary"><i class="fa fa-facebook"></i>
                                            <span class="down-mobile">Share
                                                on Facebook</span></a></li>
                                    <li><a href="#" class="tw-button btn btn-primary"><i class="fa fa-twitter"></i>
                                            <span class="down-mobile">Tweet
                                                on Twitter</span></a></li>
                                    <li><a href="#" class="gp-button btn btn-primary"><i
                                                class="fa fa-google-plus"></i></a>
                                    </li>
                                </ul>
                            </div><!-- end post-sharing -->
                            <h4><a href="{{ route('articles.show', ['article' => $post->slug]) }}"
                                    title="">{{ $post->title }}</a></h4>

                            <p>
                                {!! $post->description !!}
                            </p>

                            <small><a href="{{ route('categories.show', ['category' => $post->category->slug]) }}"
                                    title="">{{ $post->category->title }}</a></small>
                            <small>{{ $post->dateAsCarbon->diffForHumans() }}</small>
                            <small><a href="#" title="">by Jack</a></small>
                            <small><i class="fa fa-eye"></i> {{ $post->views }}</small>
                        </div><!-- end meta -->
                    </div><!-- end blog-box -->

                    <hr class="invis">
                @endforeach
            @else
                <p>Совпадений не найдено</p>
            @endif
        </div>
    </div>

    <hr class="invis">
    <div class="row">
        <div class="container col-md-12">
            <div class="pagination justify-content-center pagination-sm">
                {{ $posts->withQueryString()->onEachSide(0)->links('vendor.pagination.public') }}
            </div>
        </div>
    </div>
@endsection
