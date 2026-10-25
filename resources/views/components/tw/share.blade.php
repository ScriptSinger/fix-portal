@props(['url', 'align' => 'center'])
@php
    $btn = 'btn inline-flex h-10 items-center justify-center gap-2.5 rounded-[3px] px-4 text-sm leading-[17.5px] font-bold text-white transition-opacity hover:opacity-90';
    $encoded = urlencode($url);
@endphp
<div x-data="copyLink(@js($url))" class="flex flex-wrap gap-1 {{ $align === 'center' ? 'justify-center' : 'justify-start' }}" role="group" aria-label="Поделиться">
    <a href="https://vk.com/share.php?url={{ $encoded }}" target="_blank" rel="noopener"
        class="{{ $btn }} bg-[#3b5998] max-md:min-w-[55px]"><x-tw.icon name="vk" class="size-3.5" /><span class="max-md:hidden">Поделиться</span></a>
    <a href="https://t.me/share/url?url={{ $encoded }}" target="_blank" rel="noopener"
        class="{{ $btn }} bg-[#00b6f1] max-md:min-w-[55px]"><x-tw.icon name="telegram" class="size-3.5" /><span class="max-md:hidden">Поделиться</span></a>
    <a href="https://wa.me/?text={{ $encoded }}" target="_blank" rel="noopener" aria-label="WhatsApp"
        class="{{ $btn }} bg-[#25d366]"><x-tw.icon name="whatsapp" class="size-3.5" /></a>
    <button type="button" @click="copy()" :title="copied ? 'Скопировано' : 'Копировать ссылку'" aria-label="Копировать ссылку"
        class="{{ $btn }} bg-[#da5333]"><x-tw.icon name="link" class="size-3.5" /></button>
</div>
