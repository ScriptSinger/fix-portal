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

                    <a href="{{ route('questions.create') }}" class="btn btn-dark">Создать вопрос</a>
                </div>
                <div class="col-lg-4 col-md-12">
                    @include('public.layouts.widgets.banner.posts_search')
                </div>
            </div>
        </div>
    </section>
@endsection
@section('description', 'Коллекция статей от мастеров по ремонту бытовой техники')

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
                            <p>
                                {!! $post->description !!}
                            </p>
                            <small><a href="{{ route('categories.show', ['category' => $post->category->slug]) }}"
                                    title="">{{ $post->category->title }}</a></small>
                            <small>{{ $post->dateAsCarbon->diffForHumans() }}</small>
                            <small>{{ $post->administrator->name }}</small>
                            <small><i class="fa fa-eye"></i> {{ $post->views }}</small>
                        </div>
                    </div>
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
            <div class="pagination justify-content-center">
                {{ $posts->withQueryString()->onEachSide(0)->links('vendor.pagination.public') }}
            </div>
        </div>
    </div>
@endsection
