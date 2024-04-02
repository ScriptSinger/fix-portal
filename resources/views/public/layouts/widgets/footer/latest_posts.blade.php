<div class="widget">
    <h2 class="widget-title">Последние Статьи</h2>
    <div class="blog-list-widget">
        <div class="list-group">
            @foreach ($latest_posts as $post)
                <a href="{{ route('articles.show', ['article' => $post->slug]) }}"
                    class="list-group-item list-group-item-action flex-column align-items-start">
                    <div class="w-100 justify-content-between">
                        <img src="{{ optional($post->thumbnail)->small }}" alt="{{ $post->title }}"
                            class="img-fluid float-left">
                        <h5 class="mb-1">{{ $post->title }}</h5>
                        <small>{{ $post->dateAsCarbon->diffForHumans() }}</small>
                    </div>
            @endforeach
            </a>
        </div>
    </div>
</div>
