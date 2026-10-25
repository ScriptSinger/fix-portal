@props(['photos', 'alt' => ''])
{{-- Фото сообщения мозаикой, как в Telegram: 1 на всю ширину, 2 рядом, 3 крупное и два справа, от 4 два сверху и ряды по три. Клик открывает оригинал (data-full) --}}
@php
    $n = count($photos);
    $img = fn (array $p, string $class) => '<img src="' . e($p['src']) . '" data-full="' . e($p['full']) . '" alt="' . e($alt) . '" loading="lazy" data-lightbox-cover class="block cursor-pointer object-cover ' . $class . '">';
@endphp
@if ($n)
    <div {{ $attributes->class(['grid gap-0.5 overflow-hidden']) }}>
        @if ($n === 1)
            <div class="max-h-[34rem] overflow-hidden">{!! $img($photos[0], 'w-full') !!}</div>
        @elseif ($n === 2)
            <div class="grid grid-cols-2 gap-0.5">@foreach ($photos as $p){!! $img($p, 'aspect-[3/4] size-full') !!}@endforeach</div>
        @elseif ($n === 3)
            <div class="grid h-[min(26rem,70vw)] grid-cols-[2fr_1fr] grid-rows-1 gap-0.5">
                {!! $img($photos[0], 'size-full min-h-0') !!}
                <div class="grid min-h-0 grid-rows-2 gap-0.5">@foreach (array_slice($photos, 1) as $p){!! $img($p, 'size-full min-h-0') !!}@endforeach</div>
            </div>
        @else
            <div class="grid grid-cols-2 gap-0.5">@foreach (array_slice($photos, 0, 2) as $p){!! $img($p, 'aspect-[4/5] size-full') !!}@endforeach</div>
            <div class="grid grid-cols-3 gap-0.5">@foreach (array_slice($photos, 2) as $p){!! $img($p, 'aspect-square size-full') !!}@endforeach</div>
        @endif
    </div>
@endif
