@extends('public.layouts.app')
@section('title', config('app.name', 'Ufamasters') . ': ремонт бытовой техники, статьи, вопросы и прошивки')
@section('description', 'Коллекция статей от мастеров по ремонту бытовой техники, вопросы и ответы, база прошивок')
@section('canonical', url('/'))
@section('main-pt', 'pt-8')
@php
    $siteUrl = rtrim(config('app.url'), '/');
    $siteSchema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Organization',
                '@id' => $siteUrl . '/#organization',
                'name' => config('app.name', 'Ufamasters'),
                'url' => $siteUrl,
                'logo' => asset('assets/front/images/version/ufamasters-logo-trim.png'),
                'sameAs' => ['https://t.me/ufamasters_chat'],
            ],
            [
                '@type' => 'WebSite',
                '@id' => $siteUrl . '/#website',
                'name' => config('app.name', 'Ufamasters'),
                'url' => $siteUrl,
                'publisher' => ['@id' => $siteUrl . '/#organization'],
                'inLanguage' => 'ru-RU',
            ],
        ],
    ];
@endphp
@push('head-schema')
    <script type="application/ld+json">@json($siteSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP)</script>
@endpush

@section('above')
    <x-ui.hero />
@endsection

@section('sidebar-left')
    @include('public.partials.category-nav', ['active' => null])
@endsection

@section('content')
    <div class="mb-6 flex items-end justify-between gap-4 border-b-2 border-black pb-3">
        <h2 class="text-2xl font-bold leading-8 text-ink">Свежие статьи</h2>
        <a href="{{ route('articles.index') }}" class="keep text-sm font-bold text-black underline underline-offset-2">Все статьи &rarr;</a>
    </div>
    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 sm:gap-6">
        @foreach ($posts as $post)
            <x-ui.post-card :post="$post" show-category />
        @endforeach
    </div>
    <div class="mt-8 text-center">
        <x-ui.button variant="dark" :href="route('articles.index')">Все статьи</x-ui.button>
    </div>
@endsection

@section('sidebar')
    @include('public.partials.sidebar')
@endsection
