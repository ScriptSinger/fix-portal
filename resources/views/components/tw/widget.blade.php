@props(['title'])
<section {{ $attributes->merge(['class' => 'rounded-xl border border-line bg-white p-5']) }}>
    <h2 class="mb-4 border-b-2 border-brand-400 pb-2 text-sm font-bold uppercase tracking-wide">{{ $title }}</h2>
    {{ $slot }}
</section>
