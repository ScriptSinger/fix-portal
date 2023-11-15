@extends('public.layouts.right_sidebar')
@section('title', 'Ufamasters::' . $post->title)

@section('content')
    <div class="page-wrapper">
        <div class="blog-title-area">
            <ol class="breadcrumb hidden-xs-down">
                <li class="breadcrumb-item"><a href="{{ route('article.index') }}">Главная</a></li>
                <li class="breadcrumb-item"><a
                        href="{{ route('category.articles', ['slug' => $post->category->slug]) }}">{{ $post->category->title }}</a>
                </li>
                <li class="breadcrumb-item active">{{ $post->title }}</li>
            </ol>

            <span class="color-yellow"><a href="{{ route('category.articles', ['slug' => $post->category->slug]) }}"
                    title="">{{ $post->category->title }}</a></span>

            <h3>{{ $post->title }}</h3>

            <div class="blog-meta big-meta">
                <small>{{ $post->getPostDate() }}</small>
                <small><a href="blog-author.html" title="">by Jessica</a></small>
                <small><i class="fa fa-eye"></i> {{ $post->views }}</small>
            </div><!-- end meta -->

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

        <div class="single-post-media">
            <img src="{{ $post->getImage('thumbnail') }}" alt="" class="img-fluid">
        </div><!-- end media -->

        <div class="blog-content">
            {!! $post->content !!}
        </div><!-- end content -->

        <div class="blog-title-area">
            @isset($post->tags)
                <div class="tag-cloud-single">
                    <span>Метки</span>
                    @foreach ($post->tags as $tag)
                        <small><a href="{{ route('tag.articles', ['slug' => $tag->slug]) }}"
                                title="">{{ $tag->title }}</a></small>
                    @endforeach
                </div><!-- end meta -->
            @endisset


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

        <div class="row">
            <div class="col-lg-12">
                <div class="banner-spot clearfix">
                    <div class="banner-img">
                        <img src="upload/banner_01.jpg" alt="" class="img-fluid">
                    </div><!-- end banner-img -->
                </div><!-- end banner -->
            </div><!-- end col -->
        </div><!-- end row -->

        <hr class="invis1">

        <div class="custombox authorbox clearfix">
            <h4 class="small-title">About author</h4>
            <div class="row">
                <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
                    <img src="upload/author.jpg" alt="" class="img-fluid rounded-circle">
                </div><!-- end col -->

                <div class="col-lg-10 col-md-10 col-sm-10 col-xs-12">
                    <h4><a href="#">Jessica</a></h4>
                    <p>Quisque sed tristique felis. Lorem <a href="#">visit my website</a> amet, consectetur
                        adipiscing elit. Phasellus quis mi auctor, tincidunt nisl eget, finibus odio. Duis tempus elit quis
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
            <h4 class="small-title">You may also like</h4>
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
                            <h4><a href="marketing-single.html" title="">We are guests of ABC Design Studio</a></h4>
                            <small><a href="blog-category-01.html" title="">Trends</a></small>
                            <small><a href="blog-category-01.html" title="">21 July, 2017</a></small>
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
                            <small><a href="blog-category-01.html" title="">News</a></small>
                            <small><a href="blog-category-01.html" title="">20 July, 2017</a></small>
                        </div><!-- end meta -->
                    </div><!-- end blog-box -->
                </div><!-- end col -->
            </div><!-- end row -->
        </div><!-- end custom-box -->

        <hr class="invis1">

        <div class="custombox clearfix">
            <h4 class="small-title">{{ $post->postComment->count() }} Comments</h4>
            <div class="row">
                <div class="col-lg-12">
                    <div class="comments-list">
                        @foreach ($post->postComment as $comment)
                            <div class="media">
                                <a class="media-left" href="#">
                                    <img src="upload/author.jpg" alt="" class="rounded-circle">
                                </a>
                                <div class="media-body">
                                    <h4 class="media-heading user_name">
                                        {{ $comment->user->name }}<small>{{ $comment->dateAsCarbon->diffForHumans() }}</small>
                                    </h4>
                                    <p>{{ $comment->text }}</p>
                                    <a href="#" class="btn btn-primary btn-sm">Reply</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div><!-- end col -->
            </div><!-- end row -->
        </div><!-- end custom-box -->

        <hr class="invis1">
        @auth()
            <div class="custombox clearfix">
                <h4 class="small-title">Leave a Reply</h4>
                <div class="row">
                    <div class="col-lg-12">
                        <form class="form-wrapper" method="POST" enctype="multipart/form-data"
                            action="{{ route('article.comment.store', ['article_id' => $post->id]) }}">
                            @csrf
                            <textarea class="form-control" name="text" placeholder="Your comment"></textarea>
                            <button type="submit" class="btn btn-primary">Submit Comment</button>
                        </form>
                    </div>
                </div>
            </div>
        @endauth
    </div><!-- end page-wrapper -->


@endsection
