@props(['posts', 'showCategory' => false, 'heading' => null, 'skip' => 0])
@php($items = $posts->slice($skip))
@if ($heading)
    <h2 class="mb-6 border-b-2 border-black pb-3 text-2xl font-bold leading-8 text-ink">{{ $heading }}</h2>
@endif
@if ($items->count())
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-6">
        @foreach ($items as $post)
            <x-ui.post-card :post="$post" :show-category="$showCategory" />
        @endforeach
    </div>
@else
    <p class="bg-white p-8 text-center">Совпадений не найдено</p>
@endif
<div class="mt-10">
    {{ $posts->withQueryString()->onEachSide(1)->links('public.partials.pagination') }}
</div>
