@props(['dark' => false])
{{-- Метка поверх пунктирной рамки (small-title в старой теме) --}}
<span @class([
    'absolute -top-[19px] left-8 inline-block px-8 text-sm font-bold leading-[38px]',
    'bg-black text-white' => $dark,
    'bg-[#edeff2] text-black' => !$dark,
])>{{ $slot }}</span>
