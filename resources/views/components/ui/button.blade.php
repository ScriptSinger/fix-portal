@props([
    'variant' => 'dark', // dark | accent | telegram | outline
    'href' => null,
    'size' => 'md', // sm | md | lg
])
{{-- Единая кнопка сайта: прямые углы, Roboto Slab 14 px bold. Вид (variant) задаёт только цвет. --}}
@php
    $variants = [
        'dark' => 'bg-black text-white border border-black',
        'accent' => 'bg-brand-400 text-ink border border-brand-400',
        'telegram' => 'bg-telegram text-white border border-telegram',
        'outline' => 'bg-white text-black border border-black',
    ];
    $sizes = [
        'sm' => 'px-4 py-2 text-[13px] leading-[17px]',
        'md' => 'px-8 py-[11.8px] text-sm leading-[17.5px]',
        'lg' => 'h-[54px] px-8 text-sm leading-[17.5px]',
    ];
    $classes = 'btn inline-flex items-center justify-center gap-2 text-center font-bold focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 ' . $variants[$variant] . ' ' . $sizes[$size];
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $classes]) }}>{{ $slot }}</button>
@endif
