@extends('tw.layouts.app')
@section('title', 'Статьи | ' . config('app.name', 'Ufamasters'))
@section('description', 'Коллекция статей от мастеров по ремонту бытовой техники')
@section('canonical', url('/articles'))
@section('robots', 'noindex, follow')

@section('hero')
    <section class="relative isolate overflow-hidden bg-ink text-white"
        style="background-image: linear-gradient(rgba(17,24,39,.78), rgba(17,24,39,.78)), url('{{ optional($customization)->getImage('banner') ?? asset('assets/front/images/power_unit.jpg') }}'); background-size: cover; background-position: center;">
        <div class="mx-auto grid max-w-6xl items-center gap-8 px-4 py-10 sm:px-6 md:grid-cols-[1fr_20rem] lg:grid-cols-[1fr_22rem] lg:py-16">
            <div>
                <h1 class="text-3xl font-bold leading-tight sm:text-4xl">
                    {{ optional($customization)->title ?? 'Ремонт бытовой техники' }}</h1>
                <p class="mt-4 max-w-xl text-lg text-gray-200">
                    {{ optional($customization)->description ?? 'Узнайте как решить проблемы с бытовой техникой от опытных пользователей. Регистрируйтесь для создания своего вопроса.' }}
                </p>
                <x-tw.button :href="route('questions.create')" class="mt-6 py-3">Создать вопрос</x-tw.button>
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
