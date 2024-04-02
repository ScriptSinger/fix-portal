@isset($relatedPosts)
    <hr class="invis1">
    <div class="custombox clearfix">
        <h4 class="small-title">Вам также может понравиться</h4>
        <div class="row">
            @foreach ($relatedPosts as $post)
                <div class="col-lg-6">
                    <div class="blog-box">
                        <div class="post-media">
                            <a href="{{ route('articles.show', ['article' => $post->slug]) }}">
                                <img src="{{ $post->thumbnail->blog }}" alt="{{ $post->title }}" class="img-fluid">
                                <div class="hovereffect">
                                    <span class=""></span>
                                </div>
                            </a>
                        </div>
                        <div class="blog-meta">
                            <h4><a href="{{ route('articles.show', ['article' => $post->slug]) }}">{{ $post->title }}</a>
                            </h4>

                            <small><a href="{{ route('categories.show', ['category' => $post->category->slug]) }}">
                                    {{ $post->category->title }}
                                </a></small>

                            <small>{{ $post->dateAsCarbon->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <hr class="invis1">
@endisset
