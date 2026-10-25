@props(['title' => null, 'dark' => false])
{{-- Пунктирная рамка с язычком (custombox + small-title в старой теме) --}}
<section {{ $attributes->merge(['class' => 'relative mb-12 border border-dashed border-dashed px-8 py-12 max-sm:px-4']) }}>
    @if ($title)<x-ui.legend :dark="$dark">{{ $title }}</x-ui.legend>@endif
    {{ $slot }}
</section>
