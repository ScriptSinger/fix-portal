@extends('tw.layouts.app')
@section('title', 'Теги статей | ' . config('app.name', 'Ufamasters'))
@section('description', 'Список тегов статей по ремонту бытовой техники.')
@section('robots', 'noindex, follow')

@section('hero')
    <x-tw.page-header title="Теги статей" :items="[['Статьи', route('articles.index')], ['Теги', null]]" />
@endsection

@section('content')
    <ul class="flex flex-wrap gap-2">
        @foreach ($tags as $tag)
            <li>
                <a href="{{ route('tags.show', ['tag' => $tag->slug]) }}"
                    class="inline-flex items-center gap-2 rounded-full border border-line bg-white px-3.5 py-1.5 text-sm font-medium shadow-sm hover:border-brand-500 hover:bg-brand-50">
                    #{{ $tag->title }}
                    <span class="text-xs text-muted">{{ $tag->posts_count }}</span>
                </a>
            </li>
        @endforeach
    </ul>
@endsection

@section('sidebar')
    @include('tw.partials.sidebar')
@endsection
