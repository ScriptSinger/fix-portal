@props(['title', 'items' => [], 'description' => null, 'eyebrow' => null])
<div class="border-b border-line bg-white">
    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        @if ($items)
            <x-tw.breadcrumbs :items="$items" />
        @endif
        @if ($eyebrow)
            <p class="mt-4 text-xs font-bold uppercase tracking-wide text-brand-700">{{ $eyebrow }}</p>
        @endif
        <h1 class="{{ $eyebrow ? 'mt-1' : 'mt-4' }} text-3xl font-bold leading-tight sm:text-4xl">{{ $title }}</h1>
        @if ($description)
            <p class="mt-3 max-w-3xl text-lg text-muted">{{ $description }}</p>
        @endif
        {{ $slot }}
    </div>
</div>
