@extends('public.layouts.app')
@section('title', $firmware->meta_title . ' | ' . config('app.name', 'Ufamasters'))
@auth('web')
    @section('has-composer', '1')
@endauth
@section('description', $firmware->meta_description)
@section('canonical', route('firmwares.show', ['firmware' => $firmware->canonical_id]))
@if (! $firmware->has_file)
    @section('robots', 'noindex, nofollow')
@elseif ($firmware->is_thin)
    @section('robots', 'noindex, follow')
@endif

@php
    $jsonLd = array_filter([
        '@context' => 'https://schema.org', '@type' => 'SoftwareApplication', 'name' => $firmware->title,
        'applicationCategory' => 'Firmware', 'operatingSystem' => $firmware->platform, 'fileFormat' => $firmware->extension,
        'softwareVersion' => $firmware->model_name, 'identifier' => (string) $firmware->id, 'description' => $firmware->meta_description,
    ], fn ($v) => $v !== null && $v !== '');
    $title = $firmware->model_name ? $firmware->display_name : $firmware->title;
    $brandSlug = $firmware->brand ? \App\Services\FirmwareCatalog::slug($firmware->brand) : null;
    $crumbs = [['Прошивки', route('firmwares.index')]];
    if ($brandSlug && array_key_exists($brandSlug, \App\Services\FirmwareCatalog::brandSlugs())) {
        $crumbs[] = [$firmware->brand, route('firmwares.brand', ['brand' => $brandSlug])];
        if ($firmware->hub_url && $firmware->series) {
            $crumbs[] = [$firmware->series, $firmware->hub_url];
        }
    }
    $crumbs[] = [$title ?: ('Прошивка #' . $firmware->id), null];
    $sizeText = $firmware->size >= 1024 ? number_format($firmware->size / 1024, 1, ',', ' ') . ' КБ' : $firmware->size . ' Б';
    $platform = $firmware->platform_norm ?: $firmware->platform;
    $pairs = $firmware->parameter_pairs;
@endphp

@section('hero')
    <x-ui.page-header :title="$title" :items="$crumbs" />
@endsection

@section('sidebar-left')
    <x-ui.brand-nav :active="\App\Services\FirmwareCatalog::slug((string) $firmware->brand)" />
@endsection

@section('sidebar')
    @include('public.partials.sidebar', ['variant' => 'catalog'])
@endsection

@section('content')
    <script type="application/ld+json">@json($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP)</script>

    {{-- Главное на первом экране: что за файл и кнопка «Скачать» --}}
    <section class="bg-white p-5 sm:p-8" aria-labelledby="fw-title">
        <div class="mb-3 flex flex-wrap gap-2">
            @if ($firmware->brand)<span class="bg-brand-400 px-2.5 py-1 text-xs font-bold leading-4 text-black">{{ $firmware->brand }}</span>@endif
            @if ($platform)<span class="border border-black px-2.5 py-1 text-xs leading-4 text-black">{{ $platform }}</span>@endif
            @if ($firmware->extension)<span class="border border-black px-2.5 py-1 text-xs leading-4 text-black">.{{ ltrim($firmware->extension, '.') }}</span>@endif
        </div>
        <h2 id="fw-title" class="mb-5 break-all text-2xl font-bold leading-8 text-black">{{ $firmware->title ?: ('Прошивка #' . $firmware->id) }}</h2>

        <dl class="mb-5 grid grid-cols-2 border border-line sm:grid-cols-4">
            @foreach ([['Размер', $sizeText], ['Дата', $firmware->date], ['Платформа', $platform ?: '—'], ['Загрузки', $firmware->downloads_count]] as [$label, $value])
                <div class="border-b border-r border-line p-3 last:border-r-0 max-sm:nth-[2n]:border-r-0 sm:border-b-0">
                    <dt class="mb-1 text-[11px] uppercase tracking-[1px] text-subtle">{{ $label }}</dt>
                    <dd class="text-base font-bold text-black">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>

        <div class="flex flex-wrap items-center gap-x-5 gap-y-3">
            @if (! empty($firmware->title))
                <x-ui.button variant="dark" size="lg" :href="route('firmwares.download', ['firmware' => $firmware->id])" class="max-sm:w-full">Скачать файл</x-ui.button>
            @else
                <span class="text-subtle">Файл недоступен</span>
            @endif
            <span class="text-xs text-subtle">CRC32 <span class="break-all">{{ $firmware->crc32 }}</span> · просмотров {{ $firmware->views_count }}</span>
            <div class="sm:ml-auto"><x-ui.share compact align="start" :url="route('firmwares.show', ['firmware' => $firmware->id])" /></div>
        </div>

        <div class="mt-5 border-l-4 border-brand-400 bg-brand-50 p-4 text-sm leading-[22px]">
            Перед прошивкой сверьте код платы и версию с вашим модулем: файл не от той платы может вывести модуль из строя.
        </div>

        @if ($firmware->about_lines)
            <div class="mt-5 text-sm">
                @foreach ($firmware->about_lines as $line)<p class="mb-2">{{ $line }}</p>@endforeach
                @if ($firmware->hub_url)
                    <p class="mb-0">Другие прошивки серии <a href="{{ $firmware->hub_url }}" class="text-black underline">{{ $firmware->brand }} {{ $firmware->series }}</a>
                        (в каталоге: {{ $firmware->seriesCount() }}).
                        Все серии марки: <a href="{{ route('firmwares.brand', ['brand' => \App\Services\FirmwareCatalog::slug($firmware->brand)]) }}" class="text-black underline">{{ $firmware->brand }}</a>.</p>
                @elseif ($firmware->seriesCount() > 1)
                    <p class="mb-0">Другие прошивки серии {{ $firmware->series }}:
                        <a href="{{ route('firmwares.index', ['brand' => $firmware->brand, 'series' => $firmware->series]) }}" class="text-black underline">{{ $firmware->seriesCount() }} файлов в каталоге</a>.</p>
                @endif
            </div>
        @endif
    </section>

    @if ($pairs)
        <section class="mt-6 bg-white p-5 sm:p-8" aria-labelledby="fw-params" x-data="{ all: false }">
            <h2 id="fw-params" class="mb-4 text-[13px] font-bold uppercase tracking-[1.5px] text-ink">Параметры прошивки</h2>
            <dl class="grid gap-x-10 sm:grid-cols-2">
                @foreach ($pairs as $i => [$label, $value])
                    <div @if ($i >= 20) x-show="all" x-cloak @endif class="flex justify-between gap-4 border-b border-line py-2.5 text-sm">
                        <dt class="text-subtle">{{ $label }}</dt>
                        <dd class="text-right font-bold text-black">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
            @if (count($pairs) > 20)
                <button type="button" @click="all = !all" class="mt-4 text-sm font-bold underline" x-text="all ? 'Свернуть' : 'Показать все параметры ({{ count($pairs) }})'">Показать все параметры ({{ count($pairs) }})</button>
            @endif
        </section>
    @endif

    <div class="mt-6">
        @include('public.partials.chat', ['instance' => $firmware])
    </div>
@endsection
