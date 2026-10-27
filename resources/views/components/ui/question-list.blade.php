@props(['questions'])
@if ($questions->count())
    {{-- Кирпичная раскладка (masonry.js): карточки разной высоты встают вплотную, порядок чтения слева направо --}}
    <div data-masonry class="grid grid-cols-1 items-start gap-3 sm:grid-cols-2 sm:gap-x-6">
        @foreach ($questions as $question)
            @php $photos = array_map(fn ($p) => \App\Support\UserImages::url($p['full'], 480), \App\Support\ContentImages::extract($question->description)[1]); @endphp
            @php $excerpt = e(Str::limit(html_entity_decode(strip_tags($question->description), ENT_QUOTES | ENT_HTML5, 'UTF-8'), $photos ? 150 : 320)); @endphp
            <div>
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
    @php
        // что именно ничего не нашло: фильтр статуса, поиск по тексту или в разделе просто нет вопросов
        $status = in_array(request('status'), ['solved', 'open'], true) ? request('status') : null;
        $text = trim((string) request('text'));
        $reset = url()->current();
    @endphp
    @if ($status === 'solved')
        <x-ui.empty-state title="Решённых вопросов пока нет" text="Когда автор вопроса отметит ответ решением, вопрос появится здесь." action="Показать все вопросы" :url="$reset" />
    @elseif ($status === 'open')
        <x-ui.empty-state title="Вопросов без решения нет" text="У всех недавних вопросов уже есть отмеченное решение." action="Показать все вопросы" :url="$reset" />
    @elseif ($text !== '')
        <x-ui.empty-state :title="'По запросу «' . \Illuminate\Support\Str::limit($text, 60) . '» ничего не нашлось'" text="Попробуйте другие слова или опишите проблему новым вопросом." action="Задать вопрос" :url="route('questions.create')" secondary="Сбросить поиск" :secondary-url="$reset" />
    @else
        <x-ui.empty-state title="Здесь пока нет вопросов" text="Опишите свою проблему: ответят участники сообщества, а вопрос поможет следующим." action="Задать вопрос" :url="route('questions.create')" />
    @endif
@endif
<div class="mt-10">
    {{ $questions->withQueryString()->onEachSide(1)->links('public.partials.pagination') }}
</div>
