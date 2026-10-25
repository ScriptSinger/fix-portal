@extends('tw.layouts.app')
@section('title', "$post->title | " . config('app.name', 'Ufamasters'))
@section('description', $post->description)
@section('canonical', url('/articles/' . $post->slug))
@section('robots', 'noindex, follow')

@section('content')
    <article>
        <header>
            <x-tw.breadcrumbs :items="[
                ['Статьи', route('articles.index')],
                [$post->category->title, route('categories.show', ['category' => $post->category->slug])],
                [$post->title, null],
            ]" />

            <a href="{{ route('categories.show', ['category' => $post->category->slug]) }}"
                class="mt-5 inline-block text-xs font-bold uppercase tracking-wide text-brand-700 hover:underline">{{ $post->category->title }}</a>
            <h1 class="mt-1 text-3xl font-bold leading-tight sm:text-4xl">{{ $post->title }}</h1>

            <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1 text-[15px] text-muted">
                <time datetime="{{ $post->dateAsCarbon->toDateString() }}">{{ $post->dateAsCarbon->translatedFormat('j F Y') }}</time>
                <span>{{ $post->administrator->name }}</span>
                <x-tw.views :count="$post->views" />
            </div>

            <div class="mt-5"><x-tw.share :url="route('articles.show', ['article' => $post->slug])" /></div>
        </header>

        @if ($post->thumbnail)
            <div class="mt-8 flex justify-center overflow-hidden rounded-xl border border-line bg-white">
                <img src="{{ $post->thumbnail->blog }}" alt="{{ $post->title }}"
                    class="max-h-72 w-auto max-w-full object-contain sm:max-h-80">
            </div>
        @endif

        <div class="prose prose-lg prose-a:text-link prose-a:no-underline hover:prose-a:underline prose-img:rounded-lg mt-8 max-w-none">
            {!! $post->content !!}
        </div>

        @if (!empty($cta))
            <x-tw.card class="mt-10 border-brand-400 bg-brand-50">
                <h2 class="text-xl font-bold">{{ $cta->title ?: 'Нужен мастер в Уфе?' }}</h2>
                @if (!empty($cta->text))
                    <div class="prose mt-2 max-w-none">{!! $cta->text !!}</div>
                @endif
                <x-tw.button :href="$cta->trackedUrl()" rel="nofollow sponsored" class="mt-4">
                    {{ $cta->anchor ?: 'Подробнее' }}
                </x-tw.button>
            </x-tw.card>
        @endif

        <footer class="mt-10 space-y-5 border-t border-line pt-6">
            @if ($post->tags->isNotEmpty())
                <ul class="flex flex-wrap gap-2">
                    @foreach ($post->tags as $tag)
                        <li>
                            <a href="{{ route('tags.show', ['tag' => $tag->slug]) }}"
                                class="inline-block rounded-full bg-white px-3 py-1 text-sm text-muted ring-1 ring-line hover:bg-brand-50 hover:text-ink">#{{ $tag->title }}</a>
                        </li>
                    @endforeach
                </ul>
            @endif
            <x-tw.share :url="route('articles.show', ['article' => $post->slug])" />
        </footer>
    </article>

    @if ($relatedPosts->isNotEmpty())
        <section class="mt-12" aria-labelledby="related-title">
            <h2 id="related-title" class="mb-4 text-2xl font-bold">Вам также может понравиться</h2>
            <div class="grid gap-6 sm:grid-cols-2">
                @foreach ($relatedPosts as $related)
                    <x-tw.post-card :post="$related" />
                @endforeach
            </div>
        </section>
    @endif

    @include('tw.partials.comments', ['instance' => $post])
@endsection

@section('sidebar')
    @include('tw.partials.sidebar')
@endsection
