@extends('public.layouts.banner')
@section('title', "$post->title | " . config('app.name', 'Ufamasters'))
@section('description', $post->description)

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
            {{ Breadcrumbs::render('post', $post->category, $post) }}

            <span class="color-yellow"><a href="{{ route('categories.show', ['category' => $post->category->slug]) }}"
                    title="">{{ $post->category->title }}</a></span>
            <h3>{{ $post->title }}</h3>

            <div class="blog-meta big-meta">
                <small>{{ $post->dateAsCarbon->diffForHumans() }}</small>
                <small>{{ $post->administrator->name }}</small>
                <small><i class="fa fa-eye"></i> {{ $post->views }}</small>
            </div>

            @include('public.layouts.widgets.sharing', [
                'reference' => $post->slug,
            ])
        </div>


        <div class="blog-content">
            <div class="single-post-media">
                <img src="{{ optional($post->thumbnail)->blog }}" alt="{{ $post->title }}" class="img-fluid">
            </div>

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

        @include('public.layouts.modal.show')

    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/front/js/custom/smoothScroll.js') }}"></script>
    <script src="{{ asset('assets/front/js/custom/modal/blogModal.js') }}"></script>
@endpush
