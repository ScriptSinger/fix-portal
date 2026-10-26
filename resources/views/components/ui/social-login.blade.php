{{-- Вход через Яндекс ID и VK ID. Показываем кнопку только для тех сервисов, у которых заданы ключи приложения --}}
@props(['action' => 'Войти'])
@php($providers = \App\Services\SocialLogin::enabled())
@if ($providers)
    <div class="mb-6">
        <div class="grid gap-3 sm:grid-cols-{{ min(count($providers), 2) }}">
            @foreach ($providers as $provider => $label)
                <x-ui.button variant="outline" size="lg" :href="route('auth.redirect', $provider)" class="w-full">{{ $action }} через {{ $label }}</x-ui.button>
            @endforeach
        </div>
        <p class="mb-0 mt-5 flex items-center gap-3 text-[13px] text-subtle before:h-px before:flex-1 before:bg-[#e5e5e5] after:h-px after:flex-1 after:bg-[#e5e5e5]">или по почте</p>
    </div>
@endif
