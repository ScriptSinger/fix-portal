@php
    $city = $city ?? 'Уфе';
    $serviceType = $service_type ?? 'РемБытТехника';
    $ctaUrl = $ctaUrl ?? 'https://service.ufamasters.ru';

    $ctaSeparator = str_contains($ctaUrl, '?') ? '&' : '?';
    $ctaUrlWithUtm =
        $ctaUrl .
        $ctaSeparator .
        http_build_query([
            'utm_source' => 'ufamasters',
            'utm_medium' => 'sidebar',
            'utm_content' => 'more_details',
        ]);
@endphp

<div class="widget service-repair-widget" aria-label="Ремонт холодильников и стиральных машин на дому в Уфе">
    <div class="service-repair-widget__card">
        <div class="service-repair-widget__topline">
            <span class="service-repair-widget__badge">Ремонт в день обращения</span>
        </div>

        <div class="service-repair-widget__logo-row">
            <img src="{{ asset('assets/front/images/service/house_logo.png') }}" alt="РемБытТехника"
                class="service-repair-widget__logo">
            <div class="service-repair-widget__logo-copy">
                <div class="service-repair-widget__logo-title">{{ $serviceType }}</div>
                <div class="service-repair-widget__logo-subtitle">Сервисный центр в Уфе</div>
            </div>
        </div>

        <div class="service-repair-widget__header">
            <h2 class="widget-title service-repair-widget__title">
                Ремонт бытовой техники на дому в {{ $city }}
            </h2>
            <p class="service-repair-widget__subtitle">
                Ремонт на дому без вывоза техники. Согласование цены до начала
                работ.
            </p>
        </div>

        <div class="service-repair-widget__services" aria-label="Виды техники">

            <div class="service-repair-widget__service-card"
                style="--service-card-bg: url('{{ asset('assets/front/images/service/GwBsWpe4Y7sEB52lkj1vxQNjnMaNga9itCMLj50g.webp') }}');">
                <div class="service-repair-widget__service-body">
                    <div class="service-repair-widget__service-title">Ремонт холодильников</div>
                </div>
            </div>
            <div class="service-repair-widget__service-card"
                style="--service-card-bg: url('{{ asset('assets/front/images/service/QNLUCZoVksk6eIA22C5PoSMWz7r0e9nSk1zaV0od.webp') }}');">
                <div class="service-repair-widget__service-body">
                    <div class="service-repair-widget__service-title">Ремонт стиральных машин</div>
                </div>

            </div>

        </div>

        <div class="service-repair-widget__meta">
            <img src="{{ asset('assets/front/images/service/verify.svg') }}" alt=""
                class="service-repair-widget__verify">
            <span class="service-repair-widget__trust">Поддерживается проектом Ufamasters</span>
        </div>

        <a href="{{ $ctaUrlWithUtm }}" target="_blank" class="btn btn-primary btn-block btn-online"
            style="background-color:#0088cc; border-color:#0088cc; color:white;"
            aria-label="Подробнее о ремонте бытовой техники в {{ $city }}">

            <span class="down-mobile">Подробнее ↗</span>
        </a>

    </div>
</div>
