@php
    // Виджет партнёра оформлен в стиле его сайта service.ufamasters.ru: белый фон, системный шрифт без засечек, заголовки средней жирности
    // тёмно-серого цвета, янтарная кнопка (Tailwind yellow-500) с радиусом 4 px, фото без рамок.
    $ctaUrl = 'https://service.ufamasters.ru?' . http_build_query(['utm_source' => 'ufamasters', 'utm_medium' => 'sidebar', 'utm_content' => 'more_details']);
    $services = [
        ['Ремонт холодильников', 'GwBsWpe4Y7sEB52lkj1vxQNjnMaNga9itCMLj50g.webp'],
        ['Ремонт стиральных машин', 'QNLUCZoVksk6eIA22C5PoSMWz7r0e9nSk1zaV0od.webp'],
    ];
@endphp
<section class="mb-6 rounded-card bg-white p-5 font-service text-gray-600" aria-label="Ремонт холодильников и стиральных машин на дому в Уфе">
    <div class="flex items-center gap-3">
        <img src="{{ asset('assets/front/images/service/house_logo.png') }}" alt="РемБытТехника" class="size-14 shrink-0 object-contain">
        <div class="min-w-0">
            <div class="text-[15px] font-semibold leading-5 text-gray-900">РемБытТехника</div>
            <div class="mt-0.5 text-[13px] leading-5">Сервисный центр в Уфе</div>
        </div>
    </div>

    <h2 class="mb-2 mt-5 text-xl font-medium leading-7 text-gray-900">Ремонт бытовой техники на дому в Уфе</h2>
    <p class="m-0 text-sm leading-6">Ремонт на дому без вывоза техники. Согласование цены до начала работ.</p>
    <p class="mb-0 mt-3 inline-block rounded bg-yellow-500/15 px-2.5 py-1 text-xs font-medium text-yellow-800">Ремонт в день обращения</p>

    <div class="mt-5 grid grid-cols-2 gap-3">
        @foreach ($services as [$name, $img])
            <div>
                <img src="{{ asset('assets/front/images/service/' . $img) }}" alt="{{ $name }}" loading="lazy" class="aspect-[4/3] w-full rounded object-cover">
                <div class="mt-2 text-[13px] font-medium leading-5 text-gray-900">{{ $name }}</div>
            </div>
        @endforeach
    </div>

    <a href="{{ $ctaUrl }}" target="_blank" rel="noopener" aria-label="Заказать ремонт бытовой техники в Уфе на сайте РемБытТехника"
        class="keep mt-5 flex h-11 w-full items-center justify-center rounded bg-yellow-500 text-[15px] font-medium text-gray-900 no-underline hover:bg-yellow-400">Заказать ремонт</a>

    <div class="mt-4 flex items-center gap-2">
        <img src="{{ asset('assets/front/images/service/verify.svg') }}" alt="" class="size-[18px]">
        <span class="text-xs leading-4 text-gray-500">Поддерживается проектом Ufamasters</span>
    </div>
</section>
