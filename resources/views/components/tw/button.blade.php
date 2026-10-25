@props([
    'variant' => 'primary', // primary | dark | outline | telegram | telegram-outline
    'href' => null,
    'size' => 'md', // sm | md
])
@php
    $variants = [
        'primary' => 'bg-brand-400 text-ink',
        'dark' => 'bg-ink text-white',
        'outline' => 'border border-line bg-white text-ink',
        'telegram' => 'bg-[#0088cc] text-white',
        'telegram-outline' => 'border border-[#0088cc] bg-white text-[#0077b3]',
    ];
    $sizes = ['sm' => 'px-3 py-1.5 text-sm', 'md' => 'px-5 py-2.5'];
    $classes = 'btn inline-flex items-center justify-center gap-2 rounded-lg font-bold transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 ' . $variants[$variant] . ' ' . $sizes[$size];
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $classes]) }}>{{ $slot }}</button>
@endif
