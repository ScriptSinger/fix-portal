@extends('public.layouts.app')
@section('title', $firmware->meta_title . ' | ' . config('app.name', 'Ufamasters'))
@section('description', $firmware->meta_description)
@section('canonical', route('firmwares.show', ['firmware' => $firmware->canonical_id]))
@if (! $firmware->has_file)
    @section('robots', 'noindex, nofollow')
@elseif ($firmware->is_thin)
    @section('robots', 'noindex, follow')
@endif
@section('wide', true)

@php
    $jsonLd = array_filter([
        '@context' => 'https://schema.org', '@type' => 'SoftwareApplication', 'name' => $firmware->title,
        'applicationCategory' => 'Firmware', 'operatingSystem' => $firmware->platform, 'fileFormat' => $firmware->extension,
        'softwareVersion' => $firmware->model_name, 'identifier' => (string) $firmware->id, 'description' => $firmware->meta_description,
    ], fn ($v) => $v !== null && $v !== '');
    $title = $firmware->model_name ? $firmware->display_name : $firmware->title;
    $crumbs = [['Статьи', route('articles.index')], ['Прошивки', route('firmwares.index')]];
    if ($firmware->hub_url && $firmware->brand) {
        $crumbs[] = [$firmware->brand, route('firmwares.brand', ['brand' => \App\Services\FirmwareCatalog::slug($firmware->brand)])];
    }
    $crumbs[] = [Str::limit($firmware->model_name ?: ($firmware->title ?: ('Прошивка #' . $firmware->id)), 30, '...'), null];
@endphp

@section('hero')
    <x-ui.page-header :title="$title" :items="$crumbs" />
@endsection

@section('sidebar')
    @include('public.partials.widgets.telegram')
    @include('public.partials.widgets.repair')
    @include('public.partials.widgets.ad')
    @include('public.partials.widgets.popular')
    @include('public.partials.widgets.categories')
@endsection

@section('content')
    <script type="application/ld+json">@json($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)</script>
    <div class="rounded-[5px] bg-white p-12 max-sm:px-4">
        <div class="mb-12"><x-ui.share :url="route('firmwares.show', ['firmware' => $firmware->id])" align="start" /></div>

        <x-ui.box title="О прошивке">
            @foreach ($firmware->about_lines as $line)<p>{{ $line }}</p>@endforeach
            @if ($firmware->hub_url)
                <p>Другие прошивки серии <a href="{{ $firmware->hub_url }}" class="text-black hover:text-link">{{ $firmware->brand }} {{ $firmware->series }}</a>
                    (в каталоге: {{ $firmware->seriesCount() }}).
                    Все серии марки: <a href="{{ route('firmwares.brand', ['brand' => \App\Services\FirmwareCatalog::slug($firmware->brand)]) }}" class="text-black hover:text-link">{{ $firmware->brand }}</a>.</p>
            @elseif ($firmware->seriesCount() > 1)
                <p>Другие прошивки серии {{ $firmware->series }}:
                    <a href="{{ route('firmwares.index', ['brand' => $firmware->brand, 'series' => $firmware->series]) }}" class="text-black hover:text-link">{{ $firmware->seriesCount() }} файлов в каталоге</a>.</p>
            @endif
            <p class="mb-0 text-[13px] text-subtle">Перед прошивкой сверьте код платы и версию с вашим модулем: файл не от той платы может вывести модуль из строя.</p>
        </x-ui.box>

        <x-ui.box title="Метаданные">
            <div class="overflow-x-auto">
                <table class="data-table text-[13px]">
                    <tbody>
                        <tr><td>Размер</td><td>{{ $firmware->size >= 1024 ? number_format($firmware->size / 1024, 1, ',', ' ') . ' КБ' : $firmware->size . ' Б' }}</td></tr>
                        <tr><td>Дата</td><td>{{ $firmware->date }}</td></tr>
                        <tr><td>Расширение</td><td>{{ $firmware->extension }}</td></tr>
                        <tr><td>Платформа</td><td>{{ $firmware->platform }}</td></tr>
                        <tr><td>CRC32</td><td>{{ $firmware->crc32 }}</td></tr>
                        <tr><td>Просмотры</td><td>{{ $firmware->views_count }}</td></tr>
                        <tr><td>Загрузки</td><td>{{ $firmware->downloads_count }}</td></tr>
                    </tbody>
                </table>
            </div>
            @if (! empty($firmware->title))
                <x-ui.button variant="dark" :href="route('firmwares.download', ['firmware' => $firmware->id])">Скачать файл</x-ui.button>
            @else
                <span class="text-subtle">Файл недоступен</span>
            @endif
        </x-ui.box>

        <x-ui.box title="Параметры" dark>
            <div class="overflow-x-auto">
                <table class="data-table text-[13px]"><tbody>{!! $firmware->data !!}</tbody></table>
            </div>
        </x-ui.box>

        @include('public.partials.comments', ['instance' => $firmware])
    </div>
@endsection
