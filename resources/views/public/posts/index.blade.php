@extends('public.layouts.app')
@section('title', 'Статьи | ' . config('app.name', 'Ufamasters'))
@section('description', 'Коллекция статей от мастеров по ремонту бытовой техники')
@section('canonical', url('/articles'))
@php
    $isPaginated = request('page', 1) > 1;
    $hasFilter = request()->hasAny(['text']);
    // Журнальный блок только на первой странице без поиска
    $featured = (! $isPaginated && ! $hasFilter) ? $posts->take(3)->values() : collect();
    $card = fn ($post) => [
        'url' => route('articles.show', ['article' => $post->slug]),
        'image' => $post->thumbnail?->blog ?? asset('assets/front/upload/market_blog_01.jpg'),
        'title' => $post->title,
        'category' => $post->category->title,
        'date' => $post->dateAsCarbon,
        'author' => $post->administrator->name,
        'views' => $post->views,
    ];
@endphp
@section('robots', $isPaginated || $hasFilter ? 'noindex, follow' : 'index, follow')
@section('main-pt', 'pt-8')

@section('above')
    <header class="mb-6">
        <h1 class="text-[28px] font-bold leading-9 text-black max-sm:text-2xl">
            @if ($hasFilter)Поиск: «{{ request('text') }}»@else{{ optional($customization)->title ?? 'Ремонт бытовой техники' }}@endif
        </h1>
        @unless ($hasFilter)
            <p class="mt-1 max-w-[70ch]">{{ optional($customization)->description ?? 'Инструкции, схемы и прошивки от опытных мастеров. Вещи должны чиниться, а не выбрасываться.' }}</p>
        @endunless
    </header>
    @if ($featured->count() === 3)
        <div class="mb-10 grid gap-4 lg:grid-cols-[2fr_1fr] lg:grid-rows-2 lg:gap-6 lg:h-[480px]">
            <x-ui.feature-card big :url="$card($featured[0])['url']" :image="$card($featured[0])['image']" :title="$card($featured[0])['title']"
                :category="$card($featured[0])['category']" :date="$card($featured[0])['date']" :author="$card($featured[0])['author']" :views="$card($featured[0])['views']" />
            @foreach ($featured->slice(1) as $post)
                @php($c = $card($post))
                <x-ui.feature-card :url="$c['url']" :image="$c['image']" :title="$c['title']" :category="$c['category']" :date="$c['date']" :author="$c['author']" :views="$c['views']" />
            @endforeach
        </div>
    @endif
@endsection

@section('sidebar-left')
    @include('public.partials.category-nav', ['active' => null])
@endsection

@section('content')
    <x-ui.post-list :posts="$posts" :skip="$featured->count()" show-category :heading="$hasFilter ? null : 'Свежие статьи'" />
@endsection

@section('sidebar')
    @include('public.partials.sidebar')
@endsection
