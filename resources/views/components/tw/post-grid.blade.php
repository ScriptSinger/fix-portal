@props(['posts'])
@if ($posts->count())
    <div class="grid gap-6 sm:grid-cols-2">
        @foreach ($posts as $post)
            <x-tw.post-card :post="$post" />
        @endforeach
    </div>
    <div class="mt-10">
        {{ $posts->withQueryString()->onEachSide(1)->links('tw.partials.pagination') }}
    </div>
@else
    <p class="rounded-xl border border-line bg-white p-8 text-center text-muted">Статей пока нет</p>
@endif
