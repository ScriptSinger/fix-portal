@extends('tw.layouts.app')
@section('title', 'Категории статей | ' . config('app.name', 'Ufamasters'))
@section('description', 'Список категорий статей по ремонту бытовой техники.')
@section('robots', 'noindex, follow')

@section('hero')
    <x-tw.page-header title="Категории статей" :items="[['Статьи', route('articles.index')], ['Категории', null]]" />
@endsection

@section('content')
    <ul class="grid gap-4 sm:grid-cols-2">
        @foreach ($categories as $category)
            <li>
                <a href="{{ route('categories.show', ['category' => $category->slug]) }}"
                    class="group flex h-full items-center justify-between gap-4 rounded-xl border border-line bg-white p-5 shadow-sm transition-shadow hover:shadow-md">
                    <span class="font-display text-lg font-bold leading-snug group-hover:underline">{{ $category->title }}</span>
                    <span class="shrink-0 rounded-full bg-brand-400 px-3 py-1 text-sm font-bold"
                        title="Статей в категории">{{ $category->posts_count }}</span>
                </a>
            </li>
        @endforeach
    </ul>
@endsection

@section('sidebar')
    @include('tw.partials.sidebar')
@endsection
