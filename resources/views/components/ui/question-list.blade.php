@props(['questions'])
@if ($questions->count())
    {{-- Колонки вместо сетки: карточки разной высоты (с фото, без фото) встают вплотную, без пустых белых полей --}}
    <div class="columns-1 gap-3 sm:columns-2 sm:gap-6">
        @foreach ($questions as $question)
            @php($photos = array_map(fn ($p) => \App\Support\UserImages::url($p['full'], 480), \App\Support\ContentImages::extract($question->description)[1]))
            @php($excerpt = e(Str::limit(html_entity_decode(strip_tags($question->description), ENT_QUOTES | ENT_HTML5, 'UTF-8'), $photos ? 150 : 320)))
            <div class="mb-3 break-inside-avoid sm:mb-6">
            <x-ui.entry-card rounded :solved="$question->isSolved()"
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
            </div>
        @endforeach
    </div>
@else
    <p class="bg-white p-8 text-center">Совпадений не найдено</p>
@endif
<div class="mt-10">
    {{ $questions->withQueryString()->onEachSide(1)->links('public.partials.pagination') }}
</div>
