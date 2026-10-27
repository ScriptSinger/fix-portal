@extends('public.layouts.app')
@section('title', 'О нас | ' . config('app.name', 'Ufamasters'))
@section('description', 'Ufamasters за право на ремонт: статьи о ремонте, вопросы и ответы сообщества, база прошивок для электронных плат.')

@section('hero')
    <x-ui.page-header title="О нас" :items="[['О нас', null]]" />
@endsection

@section('content')
    @php
        $wrench = 'M21.7 6.3a5.5 5.5 0 01-7.4 6.3L6.6 20.3a2.1 2.1 0 01-3-3l7.7-7.7a5.5 5.5 0 016.3-7.4l-3.4 3.4 1 3 3 1z';
        $principles = [
            ['Ремонт лучше замены', 'Починенная техника служит дольше, а выбрасываем мы меньше.'],
            ['Знания открыты', 'Инструкции, разборы и советы доступны всем и без подписки.'],
            ['Помогаем, а не критикуем', 'Задать вопрос может любой, ответить тот, кто знает.'],
        ];
    @endphp

    <section class="relative overflow-hidden rounded-card bg-black p-5 text-white sm:p-7" aria-labelledby="about-title">
        <svg class="pointer-events-none absolute -right-6 -top-6 size-56 -rotate-12 text-brand-400/15 max-sm:size-40" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="{{ $wrench }}" /></svg>
        <p class="relative mb-3 mt-0 inline-block rounded-full bg-brand-400 px-3 py-0.5 text-[11px] font-bold uppercase tracking-[2px] text-black">Право на ремонт</p>
        <h2 id="about-title" class="relative m-0 max-w-3xl text-[26px] font-bold leading-[1.2] text-brand-400 sm:text-[32px]">Починить значит продлить жизнь вещи</h2>
        <p class="relative mb-0 mt-3 max-w-xl text-[15px] leading-7 text-white/85">
            Мы за право ремонтировать технику: понимать, как она устроена, иметь доступ к инструкциям, прошивкам и советам. Меньше выброшенного, меньше расходов, больше умелых рук. Так мы бережём вещи и уменьшаем углеродный след.
        </p>
    </section>

    <section class="mt-4 rounded-card bg-white p-4 shadow-sm sm:p-5" aria-labelledby="how">
        <h2 id="how" class="m-0 text-lg font-bold text-black">Как здесь всё устроено</h2>
        <p class="mb-0 mt-2 text-sm leading-6">Знания собраны в трёх местах: статьи с сервисной информацией, база прошивок и вопросы сообщества.</p>
        <p class="mb-0 mt-2 text-sm leading-6">
            Вопросы работают так: вы описываете поломку, сообщество отвечает, а вы отмечаете решение.
            <a href="{{ route('how-it-works') }}" class="keep font-bold text-black underline underline-offset-4">Подробнее →</a>
        </p>
    </section>

    @php $top = \App\Services\Reputation::top(5, now()->subDays(30)); @endphp
    @if ($top->isNotEmpty())
        <section id="helpers" class="mt-4 scroll-mt-28 rounded-card bg-white p-4 shadow-sm sm:p-5" aria-labelledby="helpers-title">
            <h2 id="helpers-title" class="m-0 text-lg font-bold text-black">Кто помогает в этом месяце</h2>
            <p class="mb-0 mt-1 text-[13px] text-subtle">По числу ответов, которые авторы вопросов отметили решением за последние 30 дней.</p>
            <ol class="m-0 mt-3 grid list-none gap-2 p-0">
                @foreach ($top as $row)
                    <li class="flex items-center gap-3">
                        <x-ui.avatar :user="$row['user']" size="size-9" />
                        <span class="min-w-0 flex-1 truncate font-bold text-black">{{ $row['user']->name }}</span>
                        <span class="shrink-0 rounded-full bg-green-50 px-2.5 py-0.5 text-sm font-bold text-green-700">✓ {{ $row['count'] }}</span>
                    </li>
                @endforeach
            </ol>
        </section>
    @endif

    <section class="mt-4 rounded-card bg-white p-4 shadow-sm sm:p-5" aria-labelledby="principles">
        <h2 id="principles" class="m-0 text-lg font-bold text-black">Наши принципы</h2>
        <ul class="m-0 mt-3 grid list-none gap-4 p-0 sm:grid-cols-3">
            @foreach ($principles as [$title, $text])
                <li class="flex gap-3">
                    <span class="mt-0.5 grid size-7 shrink-0 place-items-center rounded-full bg-brand-400 text-black" aria-hidden="true">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="{{ $wrench }}" /></svg>
                    </span>
                    <div class="text-sm leading-6"><b class="block text-black">{{ $title }}</b>{{ $text }}</div>
                </li>
            @endforeach
        </ul>
    </section>

    <div class="mt-4">
        <section class="rounded-card bg-brand-400 p-4 text-sm text-black">
            <h2 class="m-0 text-base font-bold">Поддержите нас</h2>
            <p class="mb-0 mt-2 leading-6">Сайт работает на платном сервере, а доход приносит реклама. Отключите, пожалуйста, блокировщик рекламы: так вы поможете проекту жить дальше.</p>
        </section>
    </div>
@endsection

@section('sidebar')
    @include('public.partials.sidebar')
@endsection
