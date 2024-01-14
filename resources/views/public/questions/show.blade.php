@extends('public.layouts.banner')
@section('title', "$question->title | " . config('app.name', 'Ufamasters'))

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
            <ol class="breadcrumb hidden-xs-down">
                {{ Breadcrumbs::render('question', $question->appliance, $question) }}
            </ol>

            <span class="color-yellow"><a
                    href="{{ route('public.appliances.show', ['appliance' => $question->appliance->slug]) }}"
                    title="">{{ $question->appliance->title }}</a></span>


            <h3>{{ $question->title }}</h3>
            <div class="blog-meta big-meta">
                <small>{{ $question->dateAsCarbon->diffForHumans() }}</small>
                <small><a href="blog-author.html" title="">{{ optional($question->user)->name }}</a></small>
                <small><i class="fa fa-eye"></i> {{ $question->views }}</small>

                @can('update', $question)
                    <small>
                        <a href="{{ route('questions.edit', ['question' => $question->slug]) }}">
                            <i class="fa fa-edit"></i>
                            <b>Редактировать</b>
                        </a>
                    </small>
                @endcan

                @can('delete', $question)
                    <small>
                        <a href="#"
                            onclick="event.preventDefault(); 
              document.getElementById('removeQuestion').submit();">
                            <i class="fa fa-trash"></i>
                            <b>Удалить</b>
                        </a>
                        <form id="removeQuestion" action="{{ route('questions.destroy', ['id' => $question->id]) }}"
                            method="POST" class="d-none">
                            @csrf
                            @method('DELETE')
                        </form>
                    </small>
                @endcan

            </div><!-- end meta -->
        </div><!-- end title -->

        @if ($question->photos)
            <div class="single-post-media">
                @foreach (json_decode($question->photos) as $photo)
                    <img src="{{ asset('storage/' . $photo) }}" alt="Photo" class="img-fluid">
                    <hr class="invis">
                @endforeach
            </div><!-- end media -->
        @endif

        <div class="blog-content mb-5">
            {!! $question->description !!}
        </div><!-- end content -->

        <div class="blog-title-area">
            <div class="post-sharing">
                <ul class="list-inline">
                    <li><a href="#" class="fb-button btn btn-primary"><i class="fa fa-facebook"></i> <span
                                class="down-mobile">Share on Facebook</span></a></li>
                    <li><a href="#" class="tw-button btn btn-primary"><i class="fa fa-twitter"></i> <span
                                class="down-mobile">Tweet on Twitter</span></a></li>
                    <li><a href="#" class="gp-button btn btn-primary"><i class="fa fa-google-plus"></i></a></li>
                </ul>
            </div><!-- end post-sharing -->
        </div><!-- end title -->

        <hr class="invis1">

        <div class="custombox authorbox clearfix">
            <h4 class="small-title">Об авторе</h4>
            <div class="row">
                <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
                    <img src="upload/author.jpg" alt="" class="img-fluid rounded-circle">
                </div><!-- end col -->

                <div class="col-lg-10 col-md-10 col-sm-10 col-xs-12">
                    <h4><a href="#">{{ optional($question->user)->name }}</a></h4>
                    <p>Quisque sed tristique felis. Lorem <a href="#">visit my website</a> amet, consectetur
                        adipiscing elit. Phasellus quis mi auctor, tincidunt nisl eget, finibus odio. Duis tempus elit
                        quis
                        risus congue feugiat. Thanks for stop Markedia!</p>

                    <div class="topsocial">
                        <a href="#" data-toggle="tooltip" data-placement="bottom" title="Facebook"><i
                                class="fa fa-facebook"></i></a>
                        <a href="#" data-toggle="tooltip" data-placement="bottom" title="Youtube"><i
                                class="fa fa-youtube"></i></a>
                        <a href="#" data-toggle="tooltip" data-placement="bottom" title="Pinterest"><i
                                class="fa fa-pinterest"></i></a>
                        <a href="#" data-toggle="tooltip" data-placement="bottom" title="Twitter"><i
                                class="fa fa-twitter"></i></a>
                        <a href="#" data-toggle="tooltip" data-placement="bottom" title="Instagram"><i
                                class="fa fa-instagram"></i></a>
                        <a href="#" data-toggle="tooltip" data-placement="bottom" title="Website"><i
                                class="fa fa-link"></i></a>
                    </div><!-- end social -->

                </div><!-- end col -->
            </div><!-- end row -->
        </div><!-- end author-box -->

        <hr class="invis1">

        <div class="custombox clearfix">
            <h4 class="small-title">Вам также может понравиться</h4>
            <div class="row">
                <div class="col-lg-6">
                    <div class="blog-box">
                        <div class="post-media">
                            <a href="marketing-single.html" title="">
                                <img src="upload/market_blog_02.jpg" alt="" class="img-fluid">
                                <div class="hovereffect">
                                    <span class=""></span>
                                </div><!-- end hover -->
                            </a>
                        </div><!-- end media -->
                        <div class="blog-meta">
                            <h4><a href="marketing-single.html" title="">We are guests of ABC Design Studio</a>
                            </h4>
                            <small><a href="blog-appliance-01.html" title="">Trends</a></small>
                            <small><a href="blog-appliance-01.html" title="">21 July, 2017</a></small>
                        </div><!-- end meta -->
                    </div><!-- end blog-box -->
                </div><!-- end col -->

                <div class="col-lg-6">
                    <div class="blog-box">
                        <div class="post-media">
                            <a href="marketing-single.html" title="">
                                <img src="upload/market_blog_03.jpg" alt="" class="img-fluid">
                                <div class="hovereffect">
                                    <span class=""></span>
                                </div><!-- end hover -->
                            </a>
                        </div><!-- end media -->
                        <div class="blog-meta">
                            <h4><a href="marketing-single.html" title="">Nostalgia at work with family</a></h4>
                            <small><a href="blog-appliance-01.html" title="">News</a></small>
                            <small><a href="blog-appliance-01.html" title="">20 July, 2017</a></small>
                        </div><!-- end meta -->
                    </div><!-- end blog-box -->
                </div><!-- end col -->
            </div><!-- end row -->
        </div><!-- end custom-box -->

        <hr class="invis1">

        @include('public.partials.comments', [
            'instance' => $question,
        ])

    </div><!-- end page-wrapper -->
@endsection
