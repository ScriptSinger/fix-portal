<div class="widget">
    <h2 class="widget-title">Популярные статьи</h2>
    <div class="blog-list-widget">
        <div class="list-group">
            @foreach ($popular_posts as $post)
                <a href="{{ route('articles.show', ['article' => $post->slug]) }}"
                    class="list-group-item list-group-item-action flex-column align-items-start">
                    <div class="w-100 justify-content-between">
                        @if ($post->thumbnail)
                            <img src="{{ $post->thumbnail }}" alt="Preview Image" class="img-fluid float-left">
                        @else
                            <img src="{{ asset('assets/front/upload/small_01.jpg') }}" class="img-fluid float-left">
                        @endif
                        <h5 class="mb-1">{{ $post->title }}</h5>
                        <small>{{ $post->dateAsCarbon->diffForHumans() }}</small>
                        <small> | <i class="fa fa-eye"></i> {{ $post->views }}</small>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>
