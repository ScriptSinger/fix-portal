@extends('public.layouts.app')
@section('title', 'Мои вопросы | ' . config('app.name', 'Ufamasters'))
@section('robots', 'noindex, nofollow')
@section('wide', '1')

@section('hero')
    <x-ui.page-header title="Мои вопросы" :items="[['Личный кабинет', route('cabinet.index')], ['Мои вопросы', null]]" />
@endsection

@section('sidebar')
    @include('public.cabinet.nav', ['active' => 'questions'])
@endsection

@section('content')
    <section class="rounded-2xl bg-white px-5 py-2 shadow-sm">
        @forelse ($questions as $question)
            @include('public.cabinet.question-row', ['question' => $question])
        @empty
            <p class="py-4 text-subtle">Вы ещё не задавали вопросов. <a href="{{ route('questions.index') }}#ask" class="font-bold text-black underline">Задать первый</a></p>
        @endforelse
    </section>
    <div class="mt-6">{{ $questions->links('public.partials.pagination') }}</div>
@endsection
