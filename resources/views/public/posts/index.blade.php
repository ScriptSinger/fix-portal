@extends('public.layouts.app')
@section('title', 'Статьи | ' . config('app.name', 'Ufamasters'))
@section('description', 'Коллекция статей от мастеров по ремонту бытовой техники')
@section('canonical', url('/articles'))
@php
    $isPaginated = request('page', 1) > 1;
    $hasFilter = request()->hasAny(['text']);
@endphp
@section('robots', $isPaginated || $hasFilter ? 'noindex, follow' : 'index, follow')
@section('main-pt', 'pt-8')

@section('above')
    @if ($hasFilter)
        <h1 class="mb-6 text-[28px] font-bold leading-9 text-black max-sm:text-2xl">Поиск: «{{ request('text') }}»</h1>
    @else
        <x-ui.hero :customization="$customization" />
    @endif
@endsection

@section('sidebar-left')
    @include('public.partials.category-nav', ['active' => null])
@endsection

@section('content')
    <x-ui.post-list :posts="$posts" show-category :heading="$hasFilter ? null : 'Свежие статьи'" />
@endsection

@section('sidebar')
    @include('public.partials.sidebar')
@endsection
