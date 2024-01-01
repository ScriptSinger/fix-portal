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

                    <a href="{{ route('questions.create') }}" class="btn btn-primary">Создать вопрос</a>
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

            @foreach ($questions as $question)
                <div class="blog-box wow fadeIn">
                    <div class="post-media">
                        <a href="{{ route('questions.index', ['slug' => $question->slug]) }}" title="">
                            @foreach (json_decode($question->photos) as $photo)
                                <img src="{{ asset('storage/' . $photo) }}" alt="Photo">
                            @break
                        @endforeach
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
                    <h4><a href="{{ route('questions.show', ['question' => $question->slug]) }}"
                            title="">{{ $question->title }}</a></h4>
                    <p> {!! $question->description !!}</p>


                    <small><a href="{{ route('public.applinaces.show', ['appliance' => $question->appliance->slug]) }}"
                            title="">{{ $question->appliance->title }}</a></small>
                    {{-- title="">{{ $post->category->title }}</a></small> --}}

                    <small>{{ $question->getCreatedDate() }}</small>
                    <small><a href="#" title="">{{ optional($question->user)->name }}</a></small>
                    <small><i class="fa fa-eye"></i> {{ $question->views }}</small>
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
            {{-- {{ $questions->onEachSide(1)->links('vendor.pagination.public') }} --}}
        </div>
    </div>
</div>
@endsection
