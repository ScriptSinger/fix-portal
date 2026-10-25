@props(['title', 'items' => [], 'description' => null, 'eyebrow' => null])
<div class="bg-footer text-white">
    <div class="mx-auto max-w-6xl px-4 py-7 sm:px-6 sm:py-9">
        @if ($items)
            <x-tw.breadcrumbs :items="$items" dark />
        @endif
        @if ($eyebrow)
            <p class="mt-4 text-xs font-bold uppercase tracking-[0.12em] text-brand-400">{{ $eyebrow }}</p>
        @endif
        <h1 class="{{ $eyebrow ? 'mt-1' : 'mt-3' }} text-3xl font-bold leading-tight sm:text-4xl">{{ $title }}</h1>
        @if ($description)
            <p class="mt-3 line-clamp-2 max-w-3xl text-lg text-gray-300 sm:line-clamp-none">{{ $description }}</p>
        @endif
        {{ $slot }}
    </div>
</div>
