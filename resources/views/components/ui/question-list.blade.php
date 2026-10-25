@props(['questions'])
@if ($questions->count())
    <div class="grid grid-cols-1 gap-3">
        @foreach ($questions as $question)
            <x-ui.entry-row
                :url="route('questions.show', ['question' => $question->slug])"
                :image="$question->srcFromContent ?: null"
                :title="$question->title"
                :excerpt="e(Str::limit(html_entity_decode(strip_tags($question->description), ENT_QUOTES | ENT_HTML5, "UTF-8"), 150))"
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
