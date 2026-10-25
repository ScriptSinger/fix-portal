@extends('public.layouts.app')
@section('title', 'Личный кабинет | ' . config('app.name', 'Ufamasters'))
@section('robots', 'noindex, nofollow')
@section('wide', '1')

@section('hero')
    <x-ui.page-header title="Личный кабинет" :items="[['Личный кабинет', null]]" />
@endsection

@section('sidebar')
    @include('public.cabinet.nav', ['active' => 'index'])
@endsection

@section('content')
    @php
        $tiles = [
            ['Новых уведомлений', $stats['unread'], route('cabinet.notifications'), true],
            ['Вопросов задано', $stats['questions'], route('cabinet.questions'), false],
            ['Ответов написано', $stats['answers'], route('cabinet.answers'), false],
            ['Реакций получено', $stats['reactions'], null, false],
        ];
    @endphp
    <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-white p-5 shadow-sm">
        <div>
            <h2 class="m-0 text-xl font-bold text-black">Здравствуйте, {{ auth()->user()->name }}</h2>
            <p class="mb-0 mt-1 text-subtle">
                @if ($stats['unread']) Новых уведомлений: {{ $stats['unread'] }}. @else Новых уведомлений нет. @endif
            </p>
        </div>
        <a href="{{ route('questions.index') }}#ask" class="keep inline-flex h-11 items-center rounded-full bg-black px-5 font-bold text-brand-400">Задать вопрос</a>
    </div>

    <div class="my-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ($tiles as [$label, $value, $url, $hot])
            @php $cls = 'block rounded-2xl p-4 shadow-sm ' . ($hot && $value ? 'bg-brand-400 text-black' : 'bg-white text-ink'); @endphp
            @if ($url)
                <a href="{{ $url }}" class="keep {{ $cls }}"><b class="block font-serif text-[28px] leading-none tabular-nums">{{ $value }}</b><span class="text-[13px] {{ $hot && $value ? 'text-black' : 'text-subtle' }}">{{ $label }}</span></a>
            @else
                <div class="{{ $cls }}"><b class="block font-serif text-[28px] leading-none tabular-nums">{{ $value }}</b><span class="text-[13px] text-subtle">{{ $label }}</span></div>
            @endif
        @endforeach
    </div>

    <section class="rounded-2xl bg-white px-5 py-2 shadow-sm" aria-labelledby="my-q">
        <div class="flex items-baseline justify-between pt-3">
            <h2 id="my-q" class="m-0 text-lg font-bold text-black">Ваши вопросы</h2>
            @if ($questions->isNotEmpty())<a href="{{ route('cabinet.questions') }}" class="text-sm font-bold text-black underline">Все</a>@endif
        </div>
        @forelse ($questions as $question)
            @include('public.cabinet.question-row', ['question' => $question])
        @empty
            <p class="py-4 text-subtle">Вы ещё не задавали вопросов.</p>
        @endforelse
    </section>
@endsection
