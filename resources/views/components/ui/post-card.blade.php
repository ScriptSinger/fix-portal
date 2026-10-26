@props(['post', 'showCategory' => false])
<x-ui.entry-card
    :url="route('articles.show', ['article' => $post->slug])"
    :image="$post->thumbnail?->blog ?? asset('assets/front/upload/market_blog_01.jpg')"
    :title="$post->title"
    :excerpt="strip_tags($post->description)"
    :date="$post->dateAsCarbon"
    :author="$post->administrator->name"
    :views="$post->views"
    :category="$showCategory ? $post->category->title : null"
    :category-url="$showCategory ? route('categories.show', ['category' => $post->category->slug]) : null" />
