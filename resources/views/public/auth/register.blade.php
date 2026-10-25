@extends('public.layouts.app')
@section('title', 'Регистрация | ' . config('app.name', 'Ufamasters'))
@section('description', 'Регистрация на UfaMasters: задавайте вопросы мастерам, комментируйте статьи и скачивайте прошивки.')
@section('robots', 'noindex, nofollow')
@section('no-sidebar', true)

@section('hero')
    <x-ui.page-header title="Регистрация" :items="[['Главная', route('articles.index')], ['Регистрация', null]]" />
@endsection

@section('content')
    <x-ui.auth-shell lead="Регистрация нужна, чтобы задавать вопросы, оставлять комментарии и скачивать прошивки.">
        <div x-data="{ agree: false, terms: false, busy: false }">
            <x-ui.notice>
                <strong>Внимание:</strong> вход через Google отключён. Если у вас уже есть аккаунт, созданный через Google, воспользуйтесь функцией
                <a href="{{ route('password.request') }}" class="keep font-bold underline">«Забыли пароль?»</a>.
            </x-ui.notice>
            <form action="{{ route('register') }}" method="post" @submit="busy = true">
                @csrf
                {{-- Ловушка для ботов: людям не видна, боты её заполняют --}}
                <div class="absolute -left-[9999px] -top-[9999px]" aria-hidden="true">
                    <label for="website">Не заполняйте это поле</label>
                    <input id="website" name="{{ \App\Support\SignupGuard::HONEYPOT_FIELD }}" type="text" tabindex="-1" autocomplete="off">
                </div>
                <input type="hidden" name="{{ \App\Support\SignupGuard::TOKEN_FIELD }}" value="{{ app(\App\Support\SignupGuard::class)->token() }}">
                <x-ui.field name="name" label="Имя" autocomplete="name" required />
                <x-ui.field name="email" type="email" label="Email" placeholder="name@example.com" autocomplete="email" required />
                <x-ui.password name="password" label="Пароль" autocomplete="new-password" />
                <x-ui.password name="password_confirmation" label="Повторите пароль" autocomplete="new-password" />
                <label class="mb-6 flex items-start gap-2">
                    <input type="checkbox" name="terms" value="agree" x-model="agree" class="mt-1.5 size-4 accent-black">
                    <span>Я согласен с <a href="#" @click.prevent="terms = true" class="font-bold text-black">условиями обработки персональных данных</a></span>
                </label>
                <x-ui.button variant="dark" class="w-full" x-bind:disabled="! agree || busy" disabled><span x-text="busy ? 'Регистрируем…' : 'Зарегистрироваться'">Зарегистрироваться</span></x-ui.button>
            </form>
            <p class="mb-0 mt-6">Уже есть аккаунт? <a href="{{ route('login') }}" class="font-bold text-black">Войти</a></p>

            <x-ui.modal id="terms" title="Согласие на обработку персональных данных">
                <div class="space-y-3 text-[14px]">@include('public.partials.consent-text')</div>
            </x-ui.modal>
        </div>
    </x-ui.auth-shell>
@endsection
