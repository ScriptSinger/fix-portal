@props(['url'])
@php
    $btn = 'inline-flex h-10 items-center justify-center gap-2 rounded-[3px] px-4 text-sm font-bold text-white transition-opacity hover:opacity-90';
    $encoded = urlencode($url);
@endphp
<div x-data="copyLink(@js($url))" class="flex flex-wrap justify-center gap-1" role="group" aria-label="Поделиться">
    <a href="https://vk.com/share.php?url={{ $encoded }}" target="_blank" rel="noopener"
        class="{{ $btn }} bg-[#3b5998]"><x-tw.icon name="vk" class="size-[18px]" />Поделиться</a>
    <a href="https://t.me/share/url?url={{ $encoded }}" target="_blank" rel="noopener"
        class="{{ $btn }} bg-[#00b6f1]"><x-tw.icon name="telegram" class="size-[18px]" />Поделиться</a>
    <a href="https://wa.me/?text={{ $encoded }}" target="_blank" rel="noopener" aria-label="WhatsApp"
        class="{{ $btn }} bg-[#25d366]"><x-tw.icon name="whatsapp" class="size-[18px]" /></a>
    <button type="button" @click="copy()" :title="copied ? 'Скопировано' : 'Копировать ссылку'" aria-label="Копировать ссылку"
        class="{{ $btn }} bg-[#da5333]"><x-tw.icon name="link" class="size-[18px]" /></button>
</div>
