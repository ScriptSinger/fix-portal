@props(['posts', 'showCategory' => false])
<div class="rounded-[5px] bg-white p-12 max-sm:px-4">
    @forelse ($posts as $post)
        <x-tw.post-card :post="$post" :show-category="$showCategory" />
    @empty
        <p>Совпадений не найдено</p>
    @endforelse
</div>
<div class="mt-8">
    {{ $posts->withQueryString()->onEachSide(1)->links('tw.partials.pagination') }}
</div>
