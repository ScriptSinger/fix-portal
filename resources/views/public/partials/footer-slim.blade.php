@php
    $currentYear = now()->year;
    $copyrightText = optional($customization)->copyright ?? "Copyright 2014-{$currentYear} Ufamasters.ru. Все права защищены.";
    $copyrightText = preg_replace('/(\b\d{4}-)\d{4}\b/u', '${1}' . $currentYear, $copyrightText);
@endphp
{{-- Лёгкий подвал для экранов входа: без списков статей, чтобы не отвлекать от формы --}}
<footer class="frame bg-black py-[30px] text-center text-[13px] text-[#9a9a9a] [--frame-line:#333]">
    <div class="site-container flex flex-col items-center justify-between gap-2 sm:flex-row">
        <span>&copy; {{ $copyrightText }}</span>
        <a href="{{ route('consent') }}" class="inline-block py-3 text-[#9a9a9a]">Согласие на обработку персональных данных</a>
    </div>
</footer>
