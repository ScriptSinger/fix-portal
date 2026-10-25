@extends('tw.layouts.app')
@section('title', 'Статьи | ' . config('app.name', 'Ufamasters'))
@section('description', 'Коллекция статей от мастеров по ремонту бытовой техники')
@section('canonical', url('/articles'))
@section('robots', 'noindex, follow')

@section('hero')
    <section class="relative isolate overflow-hidden bg-ink text-white"
        style="background-image: linear-gradient(rgba(17,24,39,.78), rgba(17,24,39,.78)), url('{{ optional($customization)->getImage('banner') ?? asset('assets/front/images/power_unit.jpg') }}'); background-size: cover; background-position: center;">
        <div class="mx-auto grid max-w-6xl items-center gap-8 px-4 py-14 sm:px-6 lg:grid-cols-[1fr_22rem] lg:py-20">
            <div>
                <h1 class="text-3xl font-bold leading-tight sm:text-4xl">
                    {{ optional($customization)->title ?? 'Ремонт бытовой техники' }}</h1>
                <p class="mt-4 max-w-xl text-lg text-gray-200">
                    {{ optional($customization)->description ?? 'Узнайте как решить проблемы с бытовой техникой от опытных пользователей. Регистрируйтесь для создания своего вопроса.' }}
                </p>
                <a href="{{ route('questions.create') }}"
                    class="mt-6 inline-flex rounded-lg bg-brand-400 px-5 py-3 font-bold text-ink transition-colors hover:bg-brand-500">Создать
                    вопрос</a>
            </div>

            <form method="GET" action="{{ route('articles.index') }}" role="search"
                class="rounded-xl bg-white p-5 text-ink shadow-lg">
                <label for="article-search" class="text-lg font-bold">Поиск статей</label>
                <p class="mt-1 text-sm text-muted">Возможно, нужная информация уже есть в статьях.</p>
                <input id="article-search" name="text" type="search" value="{{ request('text') }}"
                    placeholder="Что вы хотели бы узнать?"
                    class="mt-3 w-full rounded-lg border border-line px-3 py-2.5 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-400/40">
                <button type="submit"
                    class="mt-3 w-full rounded-lg bg-ink px-4 py-2.5 font-medium text-white hover:bg-black">Найти</button>
            </form>
        </div>
    </section>
@endsection

@section('content')
    @if ($posts->count())
        <div class="grid gap-6 sm:grid-cols-2">
            @foreach ($posts as $post)
                @include('tw.partials.post-card', ['post' => $post])
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
    <section class="rounded-xl bg-[#0088cc] p-5 text-white">
        <h2 class="font-bold">Telegram-чат сообщества</h2>
        <p class="mt-1 text-sm text-white/90">Делитесь опытом, задавайте вопросы, помогайте другим.</p>
        <a href="https://t.me/ufamasters_chat" target="_blank" rel="noopener"
            class="mt-4 block rounded-lg bg-white px-4 py-2.5 text-center text-sm font-bold text-[#0088cc] hover:bg-gray-100">Перейти
            в Telegram</a>
    </section>

    <section class="rounded-xl border border-line bg-white p-5">
        <h2 class="mb-4 border-b-2 border-brand-400 pb-2 text-sm font-bold uppercase tracking-wide">Популярные статьи
        </h2>
        <ul class="space-y-4">
            @foreach ($popular_posts as $post)
                <li class="flex gap-3">
                    <img src="{{ $post->thumbnail?->small }}" alt="" loading="lazy"
                        class="size-16 shrink-0 rounded-md bg-surface object-cover">
                    <div class="min-w-0">
                        <a href="{{ route('articles.show', ['article' => $post->slug]) }}"
                            class="line-clamp-2 text-sm font-medium leading-snug hover:underline">{{ $post->title }}</a>
                        <div class="mt-1 text-xs text-muted">{{ $post->dateAsCarbon->diffForHumans() }} ·
                            {{ $post->views }} просм.</div>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>

    <section class="rounded-xl border border-line bg-white p-5">
        <h2 class="mb-4 border-b-2 border-brand-400 pb-2 text-sm font-bold uppercase tracking-wide">Популярные категории
        </h2>
        <ul class="divide-y divide-line text-sm">
            @foreach ($popular_categories as $category)
                <li>
                    <a href="{{ route('categories.show', ['category' => $category->slug]) }}"
                        class="flex justify-between gap-3 py-2 hover:text-brand-700">
                        <span>{{ $category->title }}</span>
                        <span class="text-muted">{{ $category->posts_count }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>
@endsection
