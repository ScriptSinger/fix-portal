@php
    $card = 'mb-6 rounded-card bg-white p-5';
@endphp
@php
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
    <span class="mb-[10px] mt-0.5 inline-flex min-h-6 items-center self-start bg-[#facc15] px-2.5 text-[11px] font-bold tracking-[0.02em] text-service">Ремонт в день обращения</span>
    <div class="flex items-center gap-3">
        <img src="{{ asset('assets/front/images/service/house_logo.png') }}" alt="РемБытТехника" class="size-16 object-contain">
        <div class="min-w-0">
            <div class="text-sm font-extrabold leading-[1.3] text-service">РемБытТехника</div>
            <div class="mt-1 text-xs leading-[1.4] text-service-muted">Сервисный центр в Уфе</div>
        </div>
    </div>
    <div>
        <h2 class="mb-4 text-lg font-bold leading-[18px] text-service">Ремонт бытовой техники на дому в Уфе</h2>
        <p class="mt-2 text-xs leading-[1.55] text-service-muted">Ремонт на дому без вывоза техники. Согласование цены до начала работ.</p>
    </div>
    <div class="grid gap-2">
        @foreach ($services as [$name, $img])
            <div class="flex min-h-[138px] items-end border border-line bg-cover bg-center p-3"
                style="background-image: linear-gradient(to top, rgba(0,0,0,.72) 0%, rgba(0,0,0,.25) 55%, rgba(0,0,0,.05) 100%), url('{{ asset('assets/front/images/service/' . $img) }}')">
                <div class="text-lg font-bold leading-[1.3] text-white [text-shadow:0_1px_6px_rgba(0,0,0,.6)]">{{ $name }}</div>
            </div>
        @endforeach
    </div>
    <div class="flex items-center gap-2">
        <img src="{{ asset('assets/front/images/service/verify.svg') }}" alt="" class="size-[18px]">
        <span class="text-xs text-service-muted">Поддерживается проектом Ufamasters</span>
    </div>
    <x-ui.button variant="telegram" :href="$ctaUrl" target="_blank" rel="noopener" class="w-full" aria-label="Подробнее о ремонте бытовой техники в Уфе">Подробнее ↗</x-ui.button>
</section>
