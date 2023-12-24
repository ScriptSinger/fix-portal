<div class="widget">
    <h2 class="widget-title">Популярные статьи</h2>
    <div class="blog-list-widget">
        <div class="list-group">
            @foreach ($popular_posts as $post)
                <a href="{{ route('articles.show', ['article' => $post->slug]) }}"
                    class="list-group-item list-group-item-action flex-column align-items-start">
                    <div class="w-100 justify-content-between">
                        <img src="{{ $post->getImage('thumbnail') }}" alt="" class="img-fluid float-left">
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
