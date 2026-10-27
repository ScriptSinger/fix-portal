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
        ];
        $rank = \App\Services\Reputation::rank($stats['solutions']);
        $next = \App\Services\Reputation::next($stats['solutions']);
        $progress = \App\Services\Reputation::progress($stats['solutions']);
    @endphp
    <div class="flex flex-wrap items-center justify-between gap-4 rounded-card bg-white p-5 shadow-sm">
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
            <a href="{{ $url }}" class="keep block rounded-card p-4 shadow-sm {{ $hot && $value ? 'bg-brand-400 text-black' : 'bg-white text-ink' }}">
                <b class="block font-serif text-[28px] leading-none tabular-nums">{{ $value }}</b>
                <span class="text-[13px] {{ $hot && $value ? 'text-black' : 'text-subtle' }}">{{ $label }}</span>
            </a>
        @endforeach
        {{-- Решений: репутация, считается по ответам, которые авторы вопросов отметили решением --}}
        <div class="rounded-card bg-white p-4 text-ink shadow-sm">
            <b class="block font-serif text-[28px] leading-none tabular-nums">{{ $stats['solutions'] }}</b>
            <span class="text-[13px] text-subtle">Решений</span>
            @if ($rank)
                <span class="mt-1 block text-[13px] font-bold text-green-700">✓ {{ $rank }}</span>
            @endif
            @if ($progress !== null)
                <span class="mt-1.5 block h-1.5 overflow-hidden rounded-full bg-black/10" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ round($progress * 100) }}">
                    <span class="block h-full rounded-full bg-green-600" style="width: {{ round($progress * 100) }}%"></span>
                </span>
                <span class="mt-1 block text-[12px] leading-4 text-subtle">
                    @if ($rank) ещё {{ $next[1] }} до «{{ $next[0] }}» @else отметьте ответ решением, и появится звание @endif
                </span>
            @endif
        </div>
    </div>

    <section class="rounded-card bg-white px-5 py-2 shadow-sm" aria-labelledby="my-q">
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
