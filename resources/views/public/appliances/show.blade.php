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
            @if (count($questions))
                @foreach ($questions as $question)
                    <div class="blog-box wow fadeIn">
                        <div class="post-media" style="max-height: 400px">
                            <a href="#" role="button" data-toggle="modal" data-target="#modal{{ $question->id }}">
                                @if ($question->srcFromContent)
                                    <img src="{{ $question->srcFromContent }}" class="img-fluid" loading="lazy">
                                @else
                                    <img src="{{ asset('/assets/front/upload/market_blog_01.jpg') }}" alt="Preview Image"
                                        class="img-fluid" loading="lazy">
                                @endif

                                <div class="hovereffect">
                                    <span></span>
                                </div>
                            </a>
                        </div>
                        @include('public.layouts.modal.index', [
                            'entity' => $question,
                            'image' => $question->srcFromContent
                                ? $question->srcFromContent
                                : asset('/assets/front/upload/market_blog_01.jpg'),
                        ])


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
