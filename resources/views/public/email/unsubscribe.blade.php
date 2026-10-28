@extends('public.layouts.app')
@section('title', 'Письма об ответах | ' . config('app.name', 'Ufamasters'))
@section('description', 'Отключение писем об ответах на ваши вопросы.')
@section('robots', 'noindex, nofollow')
@section('no-sidebar', true)

@section('hero')
    <x-ui.page-header title="Письма об ответах" />
@endsection

@section('content')
    <x-ui.card class="max-w-xl">
        @if ($done)
            <p class="mb-3 text-lg font-bold text-black">Письма отключены</p>
            <p class="mb-4">Мы больше не будем присылать письма о новых ответах и решениях. Уведомления останутся в личном кабинете на сайте.</p>
            <p class="mb-0 text-[13px] text-subtle">Передумали? Включить письма обратно можно в <a href="{{ route('profile.edit') }}" class="underline">профиле</a>.</p>
        @else
            <p class="mb-4">Отключить письма о новых ответах на ваши вопросы и об отметках «решено»? Уведомления останутся в личном кабинете.</p>
            <form method="POST" action="{{ route('email.unsubscribe', ['token' => $token]) }}">
                @csrf
                <x-ui.button type="submit">Отключить письма</x-ui.button>
            </form>
        @endif
    </x-ui.card>
@endsection
