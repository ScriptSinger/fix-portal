@extends('public.layouts.app')
@section('title', 'Как это работает | ' . config('app.name', 'Ufamasters'))
@section('description', 'Как задать вопрос, получить ответ и отметить решение на Ufamasters, и как растут звания Помощник, Знаток и Эксперт.')

@section('hero')
    <x-ui.page-header title="Как это работает" :items="[['Как это работает', null]]" />
@endsection

@section('content')
    @php
        // порядок здесь настоящий: так проходит один вопрос
        $steps = [
            ['Вы', 'Опишите проблему', 'Тип техники, модель, код ошибки, что уже проверяли. Фото платы или дисплея помогают ответить точнее. Чтобы задать вопрос, нужна регистрация.'],
            ['Сообщество', 'Вам отвечают', 'Те, кто сталкивался с такой поломкой, подсказывают, что проверить и как починить. Можно уточнять и ставить реакции.'],
            ['Автор вопроса', 'Вы отмечаете правильный ответ', 'Под ответом, который помог, нажмите «Это решение». Это может сделать только автор вопроса. Если ошиблись, отметку можно снять или выбрать другой ответ.'],
            ['Результат', 'Разбор остаётся на сайте', 'Решённый вопрос помогает тем, кто придёт с такой же поломкой, а тот, кто помог, получает звание.'],
        ];
        // звания и пороги берём из тех же настроек, по которым считается репутация, чтобы текст не разошёлся с расчётом
        $ranks = \App\Services\Reputation::RANKS; // [порог => звание], от высшего к низшему
        $nodes = ['bg-black text-brand-400 border-black', 'bg-brand-400 text-black border-brand-400', 'bg-[#fdf0b8] text-ink border-brand-400'];
    @endphp

    <section class="rounded-card bg-white p-5 shadow-sm sm:p-7" aria-labelledby="how">
        <h2 id="how" class="m-0 text-xl font-bold text-black">Как это работает</h2>
        <p class="mb-0 mt-1 text-sm text-subtle">Так проходит один вопрос, от поломки до решения.</p>
        <ol class="relative m-0 mt-5 list-none p-0 before:absolute before:bottom-4 before:left-[17px] before:top-4 before:w-0.5 before:bg-line">
            @foreach ($steps as $i => [$who, $title, $text])
                @php $last = $loop->last; @endphp
                <li class="relative grid grid-cols-[2.25rem_minmax(0,1fr)] gap-4 pb-6 last:pb-0">
                    <span class="relative z-10 grid size-9 place-items-center rounded-full font-bold ring-[5px] ring-white {{ $last ? 'bg-green-700 text-white' : 'bg-black text-brand-400' }}" aria-hidden="true">{{ $last ? '✓' : $i + 1 }}</span>
                    <div>
                        <span class="mb-1 inline-block rounded-full px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider {{ $last ? 'bg-green-50 text-green-700' : 'bg-[#fdf0b8] text-ink' }}">{{ $who }}</span>
                        <b class="block text-[17px] leading-snug text-black">{{ $title }}</b>
                        <p class="m-0 mt-1 text-[14.5px] leading-6">{{ $text }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </section>

    <section class="mt-4 rounded-card bg-white p-5 shadow-sm sm:p-7" aria-labelledby="ranks">
        <h2 id="ranks" class="m-0 text-xl font-bold text-black">Звания и репутация</h2>
        <p class="mb-0 mt-1 text-sm text-subtle">Репутацию дают за пользу: один ответ, который автор отметил решением, добавляет одно очко.</p>
        <ol class="relative m-0 mt-5 list-none p-0 before:absolute before:bottom-4 before:left-[17px] before:top-4 before:w-[3px] before:rounded before:bg-gradient-to-b before:from-brand-400 before:to-line">
            @foreach ($ranks as $threshold => $title)
                <li class="relative grid grid-cols-[2.25rem_minmax(0,1fr)] items-center gap-4 pb-5 last:pb-0">
                    <span class="relative z-10 grid size-9 place-items-center rounded-full border-[3px] text-sm font-bold ring-[5px] ring-white {{ $nodes[$loop->index] ?? $nodes[2] }}" aria-hidden="true">{{ $threshold }}</span>
                    <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                        <b class="text-[17px] text-black">{{ $title }}</b>
                        <span class="rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700">от {{ $threshold }} {{ trans_choice('решения|решений|решений', $threshold, [], 'ru') }}</span>
                    </div>
                </li>
            @endforeach
        </ol>
        <p class="mb-0 mt-4 text-sm">До первого решения звания нет.</p>
        <ul class="m-0 mt-4 grid list-none gap-2 rounded-xl bg-surface p-4 text-sm">
            @foreach (['Засчитывается только отметка «Решено» от автора вопроса. Реакции в репутацию не входят.', 'Свои вопросы не считаются.', 'Число решений видно рядом с именем (например, «✓ 12»), а звание и прогресс есть в кабинете.'] as $rule)
                <li class="relative pl-5 before:absolute before:left-0 before:font-bold before:text-green-700 before:content-['✓']">{{ $rule }}</li>
            @endforeach
        </ul>
        <p class="mb-0 mt-4 text-sm"><a href="{{ route('about') }}#helpers" class="keep font-bold text-black underline underline-offset-4">Кто помогает в этом месяце →</a></p>
    </section>
@endsection

@section('sidebar')
    @include('public.partials.sidebar')
@endsection
