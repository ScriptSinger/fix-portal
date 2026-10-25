@extends('public.layouts.app')
@section('title', 'Страница не найдена | ' . config('app.name', 'Ufamasters'))
@section('description', 'Такой страницы нет. Возможно, адрес изменился.')
@section('robots', 'noindex, follow')
@section('no-sidebar', true)

@section('content')
    <div class="mx-auto max-w-[640px] rounded-[5px] bg-white px-8 py-12 text-center max-sm:px-4">
        <p class="text-[72px] font-bold leading-none text-brand-400">404</p>
        <h1 class="mt-4 text-[28px] font-bold leading-9 text-black">Страница не найдена</h1>
        <p class="mt-3">Возможно, адрес изменился или статья удалена. Попробуйте поиск или перейдите в нужный раздел.</p>

        <form method="GET" action="{{ route('articles.index') }}" role="search" class="mt-6 flex gap-2">
            <label for="e404-search" class="sr-only">Поиск по статьям</label>
            <input id="e404-search" name="text" type="search" placeholder="Ошибка или модель"
                class="h-12 min-w-0 flex-1 border border-[#dbdde0] bg-white px-4 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
            <button type="submit" class="h-12 bg-black px-6 text-sm font-bold text-brand-400">Найти</button>
        </form>

        <ul class="mt-6 flex flex-wrap justify-center gap-2">
            @foreach ([['Статьи', route('articles.index')], ['Вопросы', route('questions.index')], ['Прошивки', route('firmwares.index')], ['Главная', url('/')]] as [$label, $url])
                <li><a href="{{ $url }}" class="keep inline-flex h-10 items-center border border-black px-4 text-[13px] font-bold text-ink hover:bg-black hover:text-brand-400">{{ $label }}</a></li>
            @endforeach
        </ul>
    </div>
@endsection
