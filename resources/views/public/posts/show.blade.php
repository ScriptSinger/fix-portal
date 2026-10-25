@extends('public.layouts.app')
@section('title', "$post->title | " . config('app.name', 'Ufamasters'))
@section('description', $post->description)
@section('canonical', url('/articles/' . $post->slug))
@section('og-type', 'article')
@section('og-image', $post->thumbnail?->original ?? asset('assets/front/images/power_unit.jpg'))
@section('main-pt', 'pt-8')

@section('hero')
    <x-ui.breadcrumbs :items="[
        ['Категории', route('categories.index')],
        [$post->category->title, route('categories.show', ['category' => $post->category->slug])],
        [$post->title, null],
    ]" />
@endsection

@section('sidebar-left')
    {{-- Содержание из заголовков статьи (Alpine): показывается, если в тексте есть хотя бы два раздела --}}
    <div x-data="articleToc" x-show="items.length >= 2" x-cloak class="lg:rounded-[5px] lg:bg-white lg:p-5">
        <button type="button" @click="open = !open" :aria-expanded="open"
            class="flex w-full items-center justify-between border border-black bg-white px-4 py-3 text-left text-sm font-bold text-ink lg:hidden">
            Содержание <span aria-hidden="true" x-text="open ? '−' : '+'"></span>
        </button>
        <h2 class="mb-3 hidden text-lg font-bold leading-[18px] text-ink lg:block">Содержание</h2>
        <ol x-show="open" x-collapse class="mt-2 max-h-[calc(100vh-190px)] overflow-y-auto border border-t-0 border-line bg-white p-3 lg:mt-0 lg:border-0 lg:p-0">
            <template x-for="item in items" :key="item.id">
                <li>
                    <a :href="'#' + item.id" :aria-current="active === item.id ? 'location' : null"
                        class="keep block border-l-[3px] py-1.5 pr-1 text-[14px] leading-[19px]"
                        :class="[item.sub ? 'pl-5 text-[13px]' : 'pl-3', active === item.id ? 'border-brand-400 font-bold text-black' : 'border-transparent text-body hover:text-black']"
                        x-text="item.title"></a>
                </li>
            </template>
        </ol>
    </div>
@endsection

@section('content')
    <div class="bg-white p-5 sm:p-8 lg:p-10">
        <article>
            <header class="mb-8">
                <h1 class="mb-3 mt-3 text-[34px] font-bold leading-[42px] text-black max-sm:text-[26px] max-sm:leading-8">{{ $post->title }}</h1>

                <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-3 border-b border-line pb-5">
                    <div class="flex flex-wrap items-center gap-x-2 text-xs leading-5 text-subtle">
                        <time datetime="{{ $post->dateAsCarbon->toDateString() }}">{{ $post->dateAsCarbon->diffForHumans() }}</time>
                        <span aria-hidden="true">·</span>
                        <span>{{ $post->administrator->name }}</span>
                        <span aria-hidden="true">·</span>
                        <span class="inline-flex items-center gap-1"><x-ui.icon name="eye" class="size-3" />{{ $post->views }}</span>
                    </div>
                    <x-ui.share compact align="start" :url="route('articles.show', ['article' => $post->slug])" />
                </div>
            </header>

            @if ($post->thumbnail)
                <img src="{{ $post->thumbnail->blog }}" alt="{{ $post->title }}" data-lightbox-cover class="mb-8 block w-full cursor-pointer">
            @endif

            <div class="article-content">
                {!! \App\Support\ContentImages::withAlt($post->content, $post->title) !!}
            </div>

            @if (!empty($cta))
                <div class="article-content mt-10 border-l-4 border-brand-400 bg-brand-50 p-5 sm:p-6">
                    <h2 class="mb-3 text-xl font-bold text-black">{{ $cta->title ?: 'Нужен мастер в Уфе?' }}</h2>
                    @if (!empty($cta->text))
                        <div>{!! $cta->text !!}</div>
                    @endif
                    <x-ui.button variant="dark" :href="$cta->trackedUrl()" rel="nofollow sponsored">{{ $cta->anchor ?: 'Подробнее' }}</x-ui.button>
                </div>
            @endif

            <footer class="mt-10">
                @if ($post->tags->isNotEmpty())
                    <ul class="mb-4 flex flex-wrap gap-2">
                        @foreach ($post->tags as $tag)
                            <li><a href="{{ route('tags.show', ['tag' => $tag->slug]) }}"
                                    class="keep inline-block rounded-[3px] bg-brand-400 px-3 py-1.5 text-xs leading-4 text-black hover:underline">{{ $tag->title }}</a></li>
                        @endforeach
                    </ul>
                @endif
                <div class="flex flex-wrap items-center gap-3 text-sm text-subtle">
                    <span>Поделиться статьёй</span>
                    <x-ui.share compact align="start" :url="route('articles.show', ['article' => $post->slug])" />
                </div>
            </footer>
        </article>
    </div>

    @if ($relatedPosts->isNotEmpty())
        <section class="mt-8" aria-labelledby="related-title">
            <h2 id="related-title" class="mb-4 border-b-2 border-black pb-3 text-2xl font-bold leading-8 text-ink">Вам также может понравиться</h2>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-6">
                @foreach ($relatedPosts as $related)
                    <x-ui.post-card :post="$related" show-category />
                @endforeach
            </div>
        </section>
    @endif

    <div class="mt-8 bg-white p-5 sm:p-8 lg:p-10">
        @include('public.partials.comments', ['instance' => $post])
    </div>
@endsection

@section('sidebar')
    @include('public.partials.sidebar')
@endsection
