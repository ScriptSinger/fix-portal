@php
    $card = 'mb-12 rounded-[5px] bg-white px-8 py-12';
@endphp
@php
    $blueBtn = 'block bg-telegram px-8 py-[12.8px] text-center text-sm font-bold leading-[17.5px] text-white';
@endphp
@php
    $ctaUrl = 'https://service.ufamasters.ru?' . http_build_query(['utm_source' => 'ufamasters', 'utm_medium' => 'sidebar', 'utm_content' => 'more_details']);
    $services = [
        ['Ремонт холодильников', 'GwBsWpe4Y7sEB52lkj1vxQNjnMaNga9itCMLj50g.webp'],
        ['Ремонт стиральных машин', 'QNLUCZoVksk6eIA22C5PoSMWz7r0e9nSk1zaV0od.webp'],
    ];
@endphp
<section class="{{ $card }} flex flex-col gap-3"
    aria-label="Ремонт холодильников и стиральных машин на дому в Уфе">
    <span class="mb-[10px] mt-0.5 inline-flex min-h-6 items-center self-start bg-[#facc15] px-2.5 text-[11px] font-bold tracking-[0.02em] text-[#111827]">Ремонт в день обращения</span>
    <div class="flex items-center gap-3">
        <img src="{{ asset('assets/front/images/service/house_logo.png') }}" alt="РемБытТехника" class="size-16 object-contain">
        <div class="min-w-0">
            <div class="text-sm font-extrabold leading-[1.3] text-[#111827]">РемБытТехника</div>
            <div class="mt-1 text-xs leading-[1.4] text-[#6b7280]">Сервисный центр в Уфе</div>
        </div>
    </div>
    <div>
        <h2 class="mb-8 text-lg font-bold leading-[18px] text-[#111827]">Ремонт бытовой техники на дому в Уфе</h2>
        <p class="mt-2 text-xs leading-[1.55] text-[#6b7280]">Ремонт на дому без вывоза техники. Согласование цены до начала работ.</p>
    </div>
    <div class="grid gap-2">
        @foreach ($services as [$name, $img])
            <div class="flex min-h-[138px] items-end border border-line bg-cover bg-center p-3"
                style="background-image: linear-gradient(rgba(255,255,255,.28), rgba(255,255,255,.3)), url('{{ asset('assets/front/images/service/' . $img) }}')">
                <div class="text-lg font-bold leading-[1.3] text-[#111827]">{{ $name }}</div>
            </div>
        @endforeach
    </div>
    <div class="flex items-center gap-2">
        <img src="{{ asset('assets/front/images/service/verify.svg') }}" alt="" class="size-[18px]">
        <span class="text-xs text-[#6b7280]">Поддерживается проектом Ufamasters</span>
    </div>
    <a href="{{ $ctaUrl }}" target="_blank" rel="noopener" class="btn {{ $blueBtn }}"
        aria-label="Подробнее о ремонте бытовой техники в Уфе">Подробнее ↗</a>
</section>
