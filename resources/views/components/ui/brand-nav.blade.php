@props(['active' => null])
{{-- Марки каталога слева (общий список для страниц марки, серии и прошивки) --}}
<x-ui.side-nav heading="Марки" label="Марки прошивок" :items="collect(app(\App\Services\FirmwareCatalog::class)->brands())->map(fn ($b) => [
    'title' => $b['name'], 'url' => route('firmwares.brand', ['brand' => $b['slug']]), 'active' => $b['slug'] === $active,
])->prepend(['title' => 'Все прошивки', 'url' => route('firmwares.index'), 'active' => false])" />
