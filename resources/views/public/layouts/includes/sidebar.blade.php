<div class="sidebar">
    <div class="widget">

        @if (Request::is('profile'))
            <div class="mx-auto d-block text-center">
                <img src="{{ Auth::user()->getImage('avatar', 'assets/front/images/avatar.png') }}" alt=""
                    class="img-fluid rounded-circle mb-3">
                <div>
                    <h2 class="widget-title mb-2">{{ Auth::user()->name }}</h2>
                    <p>{{ Auth::user()->location }}</p>
                </div>
            </div>
        @else
            <h2 class="widget-title">Популярные статьи</h2>
            <div class="blog-list-widget">
                <div class="list-group">
                    @foreach ($popular_posts as $post)
                        <a href="{{ route('article.show', ['slug' => $post->slug]) }}"
                            class="list-group-item list-group-item-action flex-column align-items-start">
                            <div class="w-100 justify-content-between">
                                <img src="{{ $post->getImage('thumbnail') }}" alt=""
                                    class="img-fluid float-left">
                                <h5 class="mb-1">{{ $post->title }}</h5>
                                <small>{{ $post->getPostDate() }}</small>
                                <small> | <i class="fa fa-eye"></i> {{ $post->views }}</small>
                            </div>
                        </a>
                    @endforeach

                </div>
            </div><!-- end blog-list -->

        @endif
    </div><!-- end widget -->

    <div id="" class="widget">
        <h2 class="widget-title">Advertising</h2>
        <div class="banner-spot clearfix">
            <div class="banner-img">
                <img src="{{ asset('assets/front/upload/banner_03.jpg') }}" alt="" class="img-fluid">
            </div><!-- end banner-img -->
        </div><!-- end banner -->
    </div><!-- end widget -->

    <div class="widget">
        <h2 class="widget-title">Популярные категории</h2>
        <div class="link-widget">
            <ul>
                @foreach ($popular_categories as $category)
                    <li><a
                            href="{{ route('category.articles', ['slug' => $category->slug]) }}">{{ $category->title }}<span>{{ $category->posts_count }}</span></a>
                    </li>
                @endforeach
            </ul>
        </div><!-- end link-widget -->
    </div><!-- end widget -->
</div><!-- end sidebar -->
