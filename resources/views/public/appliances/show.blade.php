@extends('public.layouts.bar')
@section('title', "$appliance->title | " . config('app.name', 'Ufamasters'))
@section('page-title')
    <div class="page-title db">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                    <h2> {{ $appliance->title }}</h2>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 hidden-xs-down hidden-sm-down">
                    <ol class="breadcrumb">
                        {{ Breadcrumbs::render('appliance', $appliance) }}
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
        <div class="blog-custom-build">
            @foreach ($questions as $question)
                <div class="blog-box wow fadeIn">
                    <div class="post-media">
                        @if ($question->srcFromContent)
                            <div>
                                <img src="{{ $question->srcFromContent }}" alt="Preview Image" class="img-fluid">
                            </div>
                        @else
                            <img src="{{ asset('/assets/front/upload/market_blog_01.jpg') }}" alt="Preview Image"
                                class="img-fluid">
                        @endif
                        <div class="hovereffect">
                            <span></span>
                        </div>
                    </div>
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
                        </div>
                        <h4><a href="{{ route('questions.show', ['question' => $question->slug]) }}"
                                title="">{{ $question->title }}</a></h4>
                        <p>{!! Str::limit(strip_tags($question->description), 150) !!}</p>
                        <small><a href="{{ route('public.appliances.show', ['appliance' => $appliance->slug]) }}"
                                title="">{{ $appliance->title }}</a></small>
                        <small>{{ $question->dateAsCarbon->diffForHumans() }}</small>
                        <small><a href="#" title="">by Jack</a></small>
                        <small><i class="fa fa-eye"></i> {{ $question->views }}</small>
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
                {{ $questions->onEachSide(1)->links('vendor.pagination.public') }}
            </div>
        </div>
    </div>
@endsection
