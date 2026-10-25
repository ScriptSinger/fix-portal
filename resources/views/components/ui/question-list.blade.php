@props(['questions'])
<div class="rounded-[5px] bg-white p-12 max-sm:px-4">
    @forelse ($questions as $question)
        <x-ui.entry-card
            :url="route('questions.show', ['question' => $question->slug])"
            :image="$question->srcFromContent ?: asset('assets/front/upload/market_blog_01.jpg')"
            :title="$question->title"
            :excerpt="e(Str::limit(strip_tags($question->description), 150))"
            :date="$question->dateAsCarbon"
            :author="optional($question->user)->name"
            :views="$question->views"
            :category="$question->appliance->title"
            :category-url="route('public.appliances.show', ['appliance' => $question->appliance->slug])" />
    @empty
        <p>Совпадений не найдено</p>
    @endforelse
</div>
<div class="mt-8">
    {{ $questions->withQueryString()->onEachSide(1)->links('public.partials.pagination') }}
</div>
