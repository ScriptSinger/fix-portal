@props([
    'variant' => 'primary', // primary | dark | outline | telegram | telegram-outline
    'href' => null,
    'size' => 'md', // sm | md
])
@php
    $variants = [
        'primary' => 'bg-brand-400 text-ink hover:bg-brand-500',
        'dark' => 'bg-ink text-white hover:bg-black',
        'outline' => 'border border-line bg-white text-ink hover:bg-brand-50',
        'telegram' => 'bg-[#0088cc] text-white hover:bg-[#0077b3]',
        'telegram-outline' => 'border border-[#0088cc] bg-white text-[#0077b3] hover:bg-[#0088cc] hover:text-white',
    ];
    $sizes = ['sm' => 'px-3 py-1.5 text-sm', 'md' => 'px-5 py-2.5'];
    $classes = 'inline-flex items-center justify-center gap-2 rounded-lg font-bold transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 ' . $variants[$variant] . ' ' . $sizes[$size];
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $classes]) }}>{{ $slot }}</button>
@endif
