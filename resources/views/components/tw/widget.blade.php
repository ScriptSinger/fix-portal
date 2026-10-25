@props(['title', 'titleClass' => 'text-ink'])
<section {{ $attributes->merge(['class' => 'mb-12 rounded-[5px] bg-white px-8 py-12']) }}>
    <h2 class="mb-8 text-lg font-bold leading-[18px] {{ $titleClass }}">{{ $title }}</h2>
    {{ $slot }}
</section>
