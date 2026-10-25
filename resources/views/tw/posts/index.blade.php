@extends('tw.layouts.app')
@section('title', 'Статьи | ' . config('app.name', 'Ufamasters'))
@section('description', 'Коллекция статей от мастеров по ремонту бытовой техники')
@section('canonical', url('/articles'))
@section('robots', 'noindex, follow')

@section('hide-header-search', true)

@section('hero')
    <section class="relative isolate overflow-hidden bg-ink text-white"
        style="background-image: linear-gradient(rgba(17,17,17,.82), rgba(17,17,17,.82)), url('{{ optional($customization)->getImage('banner') ?? asset('assets/front/images/power_unit.jpg') }}'); background-size: cover; background-position: center;">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-12">
            <h1 class="max-w-3xl text-3xl font-bold leading-tight sm:text-4xl">
                {{ optional($customization)->title ?? 'Ремонт бытовой техники' }}</h1>
            <p class="mt-3 hidden max-w-2xl text-lg text-gray-200 sm:block">
                {{ optional($customization)->description ?? 'Узнайте как решить проблемы с бытовой техникой от опытных пользователей. Регистрируйтесь для создания своего вопроса.' }}
            </p>

            <form method="GET" action="{{ request()->is('pilot/*') ? route('pilot.articles') : route('articles.index') }}" role="search" class="mt-5 flex max-w-2xl gap-2">
                <label for="article-search" class="sr-only">Поиск статей</label>
                <input id="article-search" name="text" type="search" value="{{ request('text') }}"
                    placeholder="Ошибка или модель"
                    class="min-w-0 flex-1 rounded-lg border-0 bg-white px-4 py-3 text-ink placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-brand-400">
                <x-tw.button class="px-6">Найти</x-tw.button>
            </form>

            @if ($popular_categories->isNotEmpty())
                <ul class="mt-4 flex flex-wrap gap-2" aria-label="Популярные категории">
                    @foreach ($popular_categories->take(4) as $category)
                        <li @class(['max-sm:hidden' => $loop->index >= 3])>
                            <a href="{{ route('categories.show', ['category' => $category->slug]) }}"
                                class="inline-block rounded-full bg-white/10 px-3 py-1 text-sm text-white ring-1 ring-white/25 transition-colors hover:bg-brand-400 hover:text-ink hover:ring-brand-400">{{ $category->title }}</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>
@endsection

@section('content')
    @if ($posts->count())
        <div class="grid gap-6 sm:grid-cols-2">
            @foreach ($posts as $post)
                <x-tw.post-card :post="$post" />
            @endforeach
        </div>
        <div class="mt-10">
            {{ $posts->withQueryString()->onEachSide(1)->links('tw.partials.pagination') }}
        </div>
    @else
        <p class="rounded-xl border border-line bg-white p-8 text-center text-muted">Совпадений не найдено</p>
    @endif
@endsection

@section('sidebar')
    @include('tw.partials.sidebar')
@endsection
