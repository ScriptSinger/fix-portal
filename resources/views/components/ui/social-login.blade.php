{{-- Вход через Яндекс ID и VK ID. Кнопки в фирменном виде сервисов (цвета, значок, шрифт без засечек), чтобы их узнавали сразу.
     Показываем только те, у которых заданы ключи приложения --}}
@php($providers = \App\Services\SocialLogin::enabled())
@if ($providers)
    <div class="mb-6">
        <div class="grid gap-3">
            @isset($providers['yandex'])
                <a href="{{ route('auth.redirect', 'yandex') }}" class="flex h-12 items-center justify-center gap-3 rounded-full bg-black px-4 text-white transition hover:bg-[#2b2b2b] focus:outline-none focus-visible:ring-2 focus-visible:ring-black focus-visible:ring-offset-2" style="font-family: Arial, Helvetica, sans-serif; font-size: 16px; font-weight: 500;">
                    <svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="12" fill="#FC3F1D"/><text x="12" y="17.2" text-anchor="middle" fill="#fff" font-family="Arial, Helvetica, sans-serif" font-size="15" font-weight="700">Я</text></svg>
                    Войти с Яндекс ID
                </a>
            @endisset
            @isset($providers['vkid'])
                <a href="{{ route('auth.redirect', 'vkid') }}" class="flex h-12 items-center justify-center gap-3 rounded-full bg-[#0077FF] px-4 text-white transition hover:bg-[#0069e0] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0077FF] focus-visible:ring-offset-2" style="font-family: Arial, Helvetica, sans-serif; font-size: 16px; font-weight: 500;">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="#fff" aria-hidden="true"><path d="M12.785 16.241s.288-.032.436-.194c.136-.148.132-.427.132-.427s-.02-1.304.587-1.496c.596-.19 1.364 1.259 2.177 1.815.615.42 1.082.328 1.082.328l2.175-.03s1.137-.07.598-.964c-.044-.073-.314-.661-1.618-1.869-1.366-1.265-1.183-1.06.462-3.246.999-1.33 1.398-2.142 1.274-2.489-.12-.331-.854-.243-.854-.243l-2.448.015s-.182-.025-.316.056c-.131.079-.216.263-.216.263s-.386 1.028-.9 1.902c-1.085 1.85-1.519 1.948-1.696 1.833-.413-.267-.31-1.075-.31-1.648 0-1.793.272-2.54-.53-2.734-.266-.064-.462-.107-1.143-.114-.874-.009-1.614.003-2.033.208-.279.136-.494.44-.363.457.162.022.529.099.723.363.251.341.242 1.108.242 1.108s.144 2.11-.337 2.372c-.33.18-.783-.188-1.755-1.865-.498-.86-.874-1.81-.874-1.81s-.072-.177-.2-.272c-.155-.115-.372-.151-.372-.151l-2.325.015s-.349.01-.477.161c-.114.135-.009.413-.009.413s1.82 4.258 3.88 6.404c1.89 1.968 4.036 1.84 4.036 1.84z"/></svg>
                    Войти через VK ID
                </a>
            @endisset
        </div>
        <p class="mb-0 mt-5 flex items-center gap-3 text-[13px] text-subtle before:h-px before:flex-1 before:bg-[#e5e5e5] after:h-px after:flex-1 after:bg-[#e5e5e5]">или по почте</p>
    </div>
@endif
