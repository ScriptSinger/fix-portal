<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
                <div class="widget">
                    <h2 class="widget-title">Recent Posts</h2>
                    <div class="blog-list-widget">
                        <div class="list-group">
                            <a href="marketing-single.html"
                                class="list-group-item list-group-item-action flex-column align-items-start">
                                <div class="w-100 justify-content-between">
                                    <img src="{{ asset('assets/front/upload/small_04.jpg') }}" alt=""
                                        class="img-fluid float-left">
                                    <h5 class="mb-1">5 Beautiful buildings you need to before dying</h5>
                                    <small>12 Jan, 2016</small>
                                </div>
                            </a>

                            <a href="marketing-single.html"
                                class="list-group-item list-group-item-action flex-column align-items-start">
                                <div class="w-100 justify-content-between">
                                    <img src="{{ asset('assets/front/upload/small_05.jpg') }}" alt=""
                                        class="img-fluid float-left">
                                    <h5 class="mb-1">Let's make an introduction for creative life</h5>
                                    <small>11 Jan, 2016</small>
                                </div>
                            </a>

                            <a href="marketing-single.html"
                                class="list-group-item list-group-item-action flex-column align-items-start">
                                <div class="w-100 last-item justify-content-between">
                                    <img src="{{ asset('assets/front/upload/small_06.jpg') }}" alt=""
                                        class="img-fluid float-left">
                                    <h5 class="mb-1">Did you see the most beautiful sea in the world?</h5>
                                    <small>07 Jan, 2016</small>
                                </div>
                            </a>
                        </div>
                    </div><!-- end blog-list -->
                </div><!-- end widget -->
            </div><!-- end col -->

            <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
                <div class="widget">
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
                                        <span class="rating">
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star"></i>
                                            <i class="fa fa-star"></i>
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div><!-- end blog-list -->
                </div><!-- end widget -->
            </div><!-- end col -->

            <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
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
            </div><!-- end col -->
        </div><!-- end row -->

        <div class="row">
            <div class="col-md-12 text-center">
                <br>
                <br>
                <div class="copyright">&copy; {{ optional($customization)->copyright }}
                </div>
            </div>
        </div>
    </div><!-- end container -->
</footer><!-- end footer -->
