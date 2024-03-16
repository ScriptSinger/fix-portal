@extends('public.layouts.banner')
@section('title', 'Вопросы | ' . config('app.name', 'Ufamasters'))
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
                    @include('public.layouts.widgets.banner.questions_search')
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
            @if (count($questions))
                @foreach ($questions as $question)
                    <div class="blog-box wow fadeIn">
                        <div class="post-media" style="max-height: 400px">
                            <a href="{{ route('questions.index', ['slug' => $question->slug]) }}">
                                @if ($question->srcFromContent)
                                    <img src="{{ $question->srcFromContent }}" class="img-fluid">
                                @else
                                    <img src="{{ asset('/assets/front/upload/market_blog_01.jpg') }}" alt="Preview Image"
                                        class="img-fluid">
                                @endif

                                <div class="hovereffect">
                                    <span></span>
                                </div>
                            </a>
                        </div>
                        <!-- end media -->
                        <div class="blog-meta big-meta text-center">

                            @include('public.layouts.widgets.sharing', [
                                'reference' => $question->slug,
                            ])
                            <!-- end post-sharing -->
                            <h4><a href="{{ route('questions.show', ['question' => $question->slug]) }}"
                                    title="">{{ $question->title }}</a></h4>
                            <p>{!! Str::limit(strip_tags($question->description), 150) !!}</p>
                            <small><a
                                    href="{{ route('public.appliances.show', ['appliance' => $question->appliance->slug]) }}"
                                    title="">{{ $question->appliance->title }}</a></small>
                            <small>{{ $question->dateAsCarbon->diffForHumans() }}</small>
                            <small><a href="#" title="">{{ optional($question->user)->name }}</a></small>
                            <small><i class="fa fa-eye"></i> {{ $question->views }}</small>
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
            <div class="row justify-content-center">
                {{ $questions->withQueryString()->onEachSide(0)->links('vendor.pagination.public') }}
            </div>
        </div>
    </div>
@endsection
