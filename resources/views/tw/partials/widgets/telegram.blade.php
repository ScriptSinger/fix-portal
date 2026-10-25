@php
    $card = 'mb-12 rounded-[5px] bg-white px-8 py-12';
@endphp
@php
    $blueBtn = 'block bg-telegram px-8 py-[12.8px] text-center text-sm font-bold leading-[17.5px] text-white hover:bg-[#0077b3]';
@endphp
<section class="{{ $card }}">
    <h2 class="mb-8 flex items-center gap-2 text-lg font-bold leading-[18px] text-telegram">
        <x-tw.icon name="telegram" class="size-[18px]" /> Telegram-чат сообщества
    </h2>
    <p class="mb-[27px]">Делитесь опытом, задавайте вопросы, помогайте другим — знание сила!</p>
    <a href="https://t.me/ufamasters_chat" target="_blank" rel="noopener" class="{{ $blueBtn }}">
        <x-tw.icon name="telegram" class="mr-1 inline size-4 align-[-3px]" />Перейти в Telegram
    </a>
</section>
