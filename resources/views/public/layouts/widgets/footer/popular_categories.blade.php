<div class="widget">
    <h2 class="widget-title">Популярные категории</h2>
    <div class="link-widget">
        <ul>
            @foreach ($popular_categories as $category)
                <li><a
                        href="{{ route('categories.show', ['category' => $category->slug]) }}">{{ $category->title }}<span>{{ $category->posts_count }}</span></a>
                </li>
            @endforeach
        </ul>
    </div><!-- end link-widget -->
</div><!-- end widget -->
