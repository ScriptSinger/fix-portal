@extends('tw.layouts.auth')
@section('title', 'Регистрация | ' . config('app.name', 'Ufamasters'))
@section('content')
    <div x-data="{ agree: false, terms: false }">
        <x-tw.auth-alert register />
        <p class="mb-6 text-center">Зарегистрировать новый аккаунт</p>
        <form action="{{ route('register') }}" method="post">
            @csrf
            {{-- Ловушка для ботов: людям не видна, боты её заполняют --}}
            <div class="absolute -left-[9999px] -top-[9999px]" aria-hidden="true">
                <label for="website">Не заполняйте это поле</label>
                <input id="website" name="{{ \App\Support\SignupGuard::HONEYPOT_FIELD }}" type="text" tabindex="-1" autocomplete="off">
            </div>
            <input type="hidden" name="{{ \App\Support\SignupGuard::TOKEN_FIELD }}" value="{{ app(\App\Support\SignupGuard::class)->token() }}">
            <x-tw.auth-field name="name" placeholder="Имя" icon="user" />
            <x-tw.auth-field name="email" type="email" placeholder="Email" icon="envelope" />
            <x-tw.auth-field name="password" type="password" placeholder="Password" icon="lock" autocomplete="new-password" />
            <x-tw.auth-field name="password_confirmation" type="password" placeholder="Retype password" icon="lock" />
            <label class="mb-4 flex items-center gap-2 text-[14px]">
                <input type="checkbox" name="terms" value="agree" x-model="agree" class="size-4 accent-[#343a40]">
                <span>Я согласен с <a href="#" @click.prevent="terms = true" class="text-[#007bff] hover:underline">условиями</a></span>
            </label>
            <x-tw.auth-button x-bind:disabled="! agree" disabled>Зарегистрироваться</x-tw.auth-button>
        </form>
        <p class="mb-0 mt-4"><a href="{{ route('login') }}" class="text-[#007bff] hover:underline">У меня уже есть аккаунт</a></p>

        <x-tw.modal id="terms" title="СОГЛАСИЕ НА ОБРАБОТКУ ПЕРСОНАЛЬНЫХ ДАННЫХ">
            <div class="space-y-3 text-[14px]">@include('tw.partials.consent-text')</div>
        </x-tw.modal>
    </div>
@endsection
