@props(['questions'])
@if ($questions->count())
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-6">
        @foreach ($questions as $question)
            @php($photos = array_map(fn ($p) => \App\Support\UserImages::url($p['full'], 480), \App\Support\ContentImages::extract($question->description)[1]))
            @php($excerpt = e(Str::limit(html_entity_decode(strip_tags($question->description), ENT_QUOTES | ENT_HTML5, 'UTF-8'), 150)))
            <x-ui.entry-card placeholder
                :url="route('questions.show', ['question' => $question->slug])"
                :image="\App\Support\UserImages::url($question->srcFromContent, 480) ?: null"
                :images="$photos"
                :title="$question->title"
                :excerpt="$excerpt"
                :date="$question->dateAsCarbon"
                :author="optional($question->user)->name"
                :views="$question->views"
                :category="$question->appliance->title"
                :category-url="route('public.appliances.show', ['appliance' => $question->appliance->slug])" />
        @endforeach
    </div>
@else
    <p class="bg-white p-8 text-center">Совпадений не найдено</p>
@endif
<div class="mt-10">
    {{ $questions->withQueryString()->onEachSide(1)->links('public.partials.pagination') }}
</div>
