@extends('tw.layouts.app')
@section('title', "$post->title | " . config('app.name', 'Ufamasters'))
@section('description', $post->description)
@section('canonical', url('/articles/' . $post->slug))
@section('og-type', 'article')
@section('og-image', $post->thumbnail?->original ?? asset('assets/front/images/power_unit.jpg'))
@section('main-pt', 'pt-[51px]')

@section('content')
    <div class="rounded-[5px] bg-white p-12 max-sm:px-4">
        <article>
            <header class="mb-12">
                <x-tw.breadcrumbs :items="[
                    ['Главная', route('articles.index')],
                    ['Категории', route('categories.index')],
                    [$post->category->title, route('categories.show', ['category' => $post->category->slug])],
                ]" />
                <div class="my-2">
                    <a href="{{ route('categories.show', ['category' => $post->category->slug]) }}"
                        class="keep inline-block rounded-[3px] bg-brand-400 px-4 text-white hover:text-black">{{ $post->category->title }}</a>
                </div>
                <h1 class="mb-[7px] text-[41px] font-bold leading-[50px] text-black max-sm:text-3xl max-sm:leading-10">{{ $post->title }}</h1>

                <div class="flex flex-wrap items-center gap-x-2 text-[11px] font-bold uppercase leading-[26px] text-muted">
                    <time datetime="{{ $post->dateAsCarbon->toDateString() }}">{{ $post->dateAsCarbon->diffForHumans() }}</time>
                    <span aria-hidden="true">/</span>
                    <span>{{ $post->administrator->name }}</span>
                    <span aria-hidden="true">/</span>
                    <span class="inline-flex items-center gap-1"><x-tw.icon name="eye" class="size-3" />{{ $post->views }}</span>
                </div>

                <div class="mt-4"><x-tw.share :url="route('articles.show', ['article' => $post->slug])" align="start" /></div>
            </header>

            @if ($post->thumbnail)
                <img src="{{ $post->thumbnail->blog }}" alt="{{ $post->title }}" data-lightbox-cover class="mb-10 block w-full cursor-pointer">
            @endif

            <div class="article-content">
                {!! $post->content !!}
            </div>

            @if (!empty($cta))
                <div class="article-content mt-12 border border-dashed border-[#dadada] px-8 py-12">
                    <h2 class="mb-4 text-xl font-bold text-black">{{ $cta->title ?: 'Нужен мастер в Уфе?' }}</h2>
                    @if (!empty($cta->text))
                        <div>{!! $cta->text !!}</div>
                    @endif
                    <x-tw.button variant="dark" :href="$cta->trackedUrl()" rel="nofollow sponsored">{{ $cta->anchor ?: 'Подробнее' }}</x-tw.button>
                </div>
            @endif

            <footer class="mt-12">
                @if ($post->tags->isNotEmpty())
                    <ul class="mb-6 flex flex-wrap">
                        @foreach ($post->tags as $tag)
                            <li class="my-2 mr-2 inline-block h-7 rounded-[3px] bg-brand-400 px-4">
                                <a href="{{ route('tags.show', ['tag' => $tag->slug]) }}"
                                    class="keep mt-[1px] inline-block px-4 text-[11px] uppercase leading-7 tracking-[2px] text-white hover:text-black">{{ $tag->title }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <x-tw.share :url="route('articles.show', ['article' => $post->slug])" align="start" />
            </footer>
        </article>


    @if ($relatedPosts->isNotEmpty())
        <section class="mt-12 relative border border-dashed border-[#dadada] px-8 py-12 max-sm:px-4" aria-label="Похожие статьи">
            <x-tw.legend>Вам также может понравиться</x-tw.legend>
            <div class="grid gap-8 sm:grid-cols-2">
                @foreach ($relatedPosts as $related)
                    <article>
                        <a href="{{ route('articles.show', ['article' => $related->slug]) }}" class="block overflow-hidden">
                            <img src="{{ $related->thumbnail?->blog ?? asset('assets/front/upload/market_blog_01.jpg') }}" alt="" loading="lazy" class="block w-full">
                        </a>
                        <h3 class="mt-4 text-lg font-bold leading-7 text-black"><a href="{{ route('articles.show', ['article' => $related->slug]) }}">{{ $related->title }}</a></h3>
                        <p class="text-[11px] uppercase leading-[26px] text-[#999]">{{ $related->category->title }} / {{ $related->dateAsCarbon->diffForHumans() }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

        @include('tw.partials.comments', ['instance' => $post])
    </div>
@endsection

@section('sidebar')
    @include('tw.partials.sidebar')
@endsection
