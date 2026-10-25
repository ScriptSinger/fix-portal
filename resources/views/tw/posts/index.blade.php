@extends('tw.layouts.app')
@section('title', 'Статьи | ' . config('app.name', 'Ufamasters'))
@section('description', 'Коллекция статей от мастеров по ремонту бытовой техники')
@section('canonical', url('/articles'))
@section('robots', 'noindex, follow')

@section('hero')
    <section class="bg-cover bg-center pb-32 pt-[119px] lg:pb-[139px] lg:pt-[115px]"
        style="background-image: url('{{ optional($customization)->getImage('banner') ?? asset('assets/front/images/power_unit.jpg') }}')">
        <div class="mx-auto max-w-[1140px] px-[15px]">
            <div class="grid items-center gap-8 lg:gap-[30px] lg:grid-cols-[minmax(0,1fr)_350px]">
                <div>
                    <h1 class="mb-8 text-[48px] font-bold leading-[44px] text-white max-sm:text-[34px]">
                        {{ optional($customization)->title ?? 'Ремонт бытовой техники' }}</h1>
                    <p class="mb-8 text-base leading-7 text-white lg:mr-16">
                        {{ optional($customization)->description ?? 'Узнайте как решить проблемы с бытовой техникой от опытных пользователей. Регистрируйтесь для создания своего вопроса.' }}
                    </p>
                    <a href="{{ route('questions.create') }}"
                        class="inline-block bg-black px-8 py-[12.8px] text-sm font-bold leading-[17.5px] text-white hover:bg-[#222]">Создать вопрос</a>
                </div>

                <form method="GET" role="search"
                    action="{{ request()->is('pilot/*') ? route('pilot.articles') : route('articles.index') }}"
                    class="relative border-[6px] border-brand-400 bg-brand-400 px-8 py-12 text-center before:pointer-events-none before:absolute before:inset-0 before:border before:border-black">
                    <h2 class="mb-[17px] text-[22px] font-bold leading-[30.8px] text-black">Поиск статей</h2>
                    <p class="mb-8 text-black/90">Возможно, нужная информация уже есть в статьях. Попробуйте поиск</p>
                    <label for="article-search" class="sr-only">Поиск статей</label>
                    <input id="article-search" name="text" type="search" value="{{ request('text') }}"
                        placeholder="Что вы хотели бы узнать?"
                        class="mb-4 block h-14 w-full border border-[#dbdde0] bg-white py-2 pl-6 pr-3 text-sm text-[#464a4c] placeholder:text-[#464a4c] focus:outline-none">
                    <button type="submit"
                        class="block w-full bg-black px-8 py-[12.8px] text-sm font-bold leading-[17.5px] text-white hover:bg-[#222]">Найти</button>
                </form>
            </div>
        </div>
    </section>
@endsection

@section('content')
    <x-tw.post-list :posts="$posts" show-category />
@endsection

@section('sidebar')
    @include('tw.partials.sidebar')
@endsection
