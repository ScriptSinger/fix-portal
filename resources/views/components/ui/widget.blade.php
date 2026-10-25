@props(['title', 'titleClass' => 'text-ink'])
<section {{ $attributes->merge(['class' => 'mb-6 rounded-[5px] bg-white p-5']) }}>
    <h2 class="mb-4 text-lg font-bold leading-[18px] {{ $titleClass }}">{{ $title }}</h2>
    {{ $slot }}
</section>
