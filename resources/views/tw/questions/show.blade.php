@extends('tw.layouts.app')
@section('title', "$question->title | " . config('app.name', 'Ufamasters'))
@section('description', Str::limit(strip_tags($question->description ?? ''), 160))
@section('main-pt', 'pt-[51px]')

@php
    $url = route('questions.show', ['question' => $question->slug]);
    $photos = $question->photos ? (json_decode($question->photos, true) ?: []) : [];
@endphp

@section('content')
    <div class="rounded-[5px] bg-white p-12 max-sm:px-4">
        <article>
            <header class="mb-12">
                <x-tw.breadcrumbs :items="[
                    ['Главная', route('articles.index')],
                    ['Вопросы', route('questions.index')],
                    [$question->appliance->title, route('public.appliances.show', ['appliance' => $question->appliance->slug])],
                ]" />
                <div class="my-2">
                    <a href="{{ route('public.appliances.show', ['appliance' => $question->appliance->slug]) }}"
                        class="keep inline-block rounded-[3px] bg-brand-400 px-4 text-white hover:text-black">{{ $question->appliance->title }}</a>
                </div>
                <h1 class="mb-[7px] text-[41px] font-bold leading-[50px] text-black max-sm:text-3xl max-sm:leading-10">{{ $question->title }}</h1>

                <div class="flex flex-wrap items-center gap-x-2 text-[11px] font-bold uppercase leading-[26px] text-muted">
                    <time datetime="{{ $question->dateAsCarbon->toDateString() }}">{{ $question->dateAsCarbon->diffForHumans() }}</time>
                    <span aria-hidden="true">/</span>
                    <span>{{ optional($question->user)->name }}</span>
                    <span aria-hidden="true">/</span>
                    <span class="inline-flex items-center gap-1"><x-tw.icon name="eye" class="size-3" />{{ $question->views }}</span>
                    @can('update', $question)
                        <span aria-hidden="true">/</span>
                        <a href="{{ route('questions.edit', ['question' => $question->slug]) }}" class="text-ink hover:text-link">Редактировать</a>
                    @endcan
                    @can('delete', $question)
                        <span aria-hidden="true">/</span>
                        <form method="POST" action="{{ route('questions.destroy', ['question' => $question->id]) }}"
                            @submit="if (! confirm('Удалить вопрос?')) $event.preventDefault()" x-data>
                            @csrf @method('DELETE')
                            <button type="submit" class="font-bold uppercase text-ink hover:text-link">Удалить</button>
                        </form>
                    @endcan
                </div>

                <div class="mt-4"><x-tw.share :url="$url" align="start" /></div>
            </header>

            @foreach ($photos as $photo)
                <img src="{{ asset('storage/' . $photo) }}" alt="" data-lightbox-cover class="mb-10 block w-full cursor-pointer">
            @endforeach

            <div class="article-content mb-12">{!! $question->description !!}</div>
        </article>

        @include('tw.partials.comments', ['instance' => $question])
    </div>
@endsection

@section('sidebar')
    @include('tw.partials.sidebar')
@endsection
