@extends('public.layouts.banner')
@section('title', "$post->title | " . config('app.name', 'Ufamasters'))

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
                {{ Breadcrumbs::render('post', $post->category, $post) }}
            </ol>
            <span class="color-yellow"><a href="{{ route('categories.show', ['category' => $post->category->slug]) }}"
                    title="">{{ $post->category->title }}</a></span>
            <h3>{{ $post->title }}</h3>

            <div class="blog-meta big-meta">
                <small>{{ $post->dateAsCarbon->diffForHumans() }}</small>
                <small><a href="blog-author.html" title="">by Jessica</a></small>
                <small><i class="fa fa-eye"></i> {{ $post->views }}</small>
            </div>

            @include('public.layouts.widgets.sharing', [
                'reference' => $post->slug,
            ])
        </div>

        <div class="single-post-media">
            <img src="{{ $post->thumbnail }}" alt="Preview Image" class="img-fluid">
        </div>

        <div class="blog-content">
            {!! $post->content !!}
        </div>

        <div class="blog-title-area">
            @isset($post->tags)
                <div class="tag-cloud-single">
                    @foreach ($post->tags as $tag)
                        <span><a href="{{ route('tag.articles', ['slug' => $tag->slug]) }}"
                                title="">{{ $tag->title }}</a></span>
                    @endforeach
                </div>
            @endisset
            @include('public.layouts.widgets.sharing', [
                'reference' => $post->slug,
            ])
        </div>

        @include('public.layouts.widgets.page.related_posts', ['relatedPosts' => $relatedPosts])

        @include('public.layouts.widgets.page.comments', [
            'instance' => $post,
        ])

    </div>
@endsection
