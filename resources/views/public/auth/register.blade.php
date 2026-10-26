@extends('public.layouts.app')
@section('title', 'Регистрация | ' . config('app.name', 'Ufamasters'))
@section('description', 'Регистрация на UfaMasters: задавайте вопросы мастерам, комментируйте статьи и скачивайте прошивки.')
@section('robots', 'noindex, nofollow')
@section('bare', true)
@section('slim-footer', true)
@section('main-pt', 'pt-0 sm:pt-12')

@section('content')
    <x-ui.auth-shell title="Регистрация" eyebrow="Право на ремонт" headline="Вещи должны чиниться, а не выбрасываться." :points="['Ремонт экономит деньги', 'Ремонт бережёт ресурсы планеты', 'Ремонт учит разбираться в технике', 'Ремонт — право каждого владельца']">
        <div x-data="{ agree: false, terms: false, busy: false }">
            <x-ui.social-login action="Зарегистрироваться" />
            <form action="{{ route('register') }}" method="post" @submit="busy = true">
                @csrf
                {{-- Ловушка для ботов: людям не видна, боты её заполняют --}}
                <div class="absolute -left-[9999px] -top-[9999px]" aria-hidden="true">
                    <label for="website">Не заполняйте это поле</label>
                    <input id="website" name="{{ \App\Support\SignupGuard::HONEYPOT_FIELD }}" type="text" tabindex="-1" autocomplete="off">
                </div>
                <input type="hidden" name="{{ \App\Support\SignupGuard::TOKEN_FIELD }}" value="{{ app(\App\Support\SignupGuard::class)->token() }}">
                <x-ui.field name="name" label="Имя" autocomplete="name" required large />
                <x-ui.field name="email" type="email" label="Email" placeholder="name@example.com" autocomplete="email" required large />
                <x-ui.password name="password" label="Пароль" autocomplete="new-password" />
                <x-ui.password name="password_confirmation" label="Повторите пароль" autocomplete="new-password" />
                <label class="mb-6 flex items-start gap-3">
                    <input type="checkbox" name="terms" value="agree" x-model="agree" class="mt-1 size-5 shrink-0 accent-black">
                    <span class="text-[14px] leading-6">Я согласен с <a href="#" @click.prevent="terms = true" class="font-bold text-black underline">условиями обработки персональных данных</a></span>
                </label>
                <x-ui.button variant="dark" class="h-12 w-full !py-0" x-bind:disabled="! agree || busy" disabled><span x-text="busy ? 'Регистрируем…' : 'Зарегистрироваться'">Зарегистрироваться</span></x-ui.button>
            </form>
            <p class="mb-0 mt-8 border-t border-[#e5e5e5] pt-6">Уже есть аккаунт? <a href="{{ route('login') }}" class="font-bold text-black underline">Войти</a></p>
            <p class="mb-0 mt-4 text-[13px] leading-5 text-subtle">Вход через Google отключён. Если у вас был аккаунт через Google, воспользуйтесь «Забыли пароль?» на странице входа.</p>

            <x-ui.modal id="terms" title="Согласие на обработку персональных данных">
                <div class="space-y-3 text-[14px]">@include('public.partials.consent-text')</div>
            </x-ui.modal>
        </div>
    </x-ui.auth-shell>
@endsection
