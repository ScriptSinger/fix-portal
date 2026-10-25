@php
    $currentYear = now()->year;
    $copyrightText = optional($customization)->copyright ?? "Copyright 2014-{$currentYear} Ufamasters.ru. Все права защищены.";
    $copyrightText = preg_replace('/(\b\d{4}-)\d{4}\b/u', '${1}' . $currentYear, $copyrightText);
@endphp
{{-- Лёгкий подвал для экранов входа: без списков статей, чтобы не отвлекать от формы --}}
<footer class="border-[6px] border-black bg-black py-6 text-center text-[13px] text-[#9a9a9a] shadow-[inset_0_0_0_1px_#333]">
    <div class="mx-auto flex max-w-[1140px] flex-col items-center justify-between gap-2 px-[15px] sm:flex-row">
        <span>&copy; {{ $copyrightText }}</span>
        <a href="{{ route('consent') }}" class="text-[#9a9a9a]">Согласие на обработку персональных данных</a>
    </div>
</footer>
