@extends('public.layouts.app')
@section('title', "$question->title | " . config('app.name', 'Ufamasters'))
@section('description', Str::limit(trim(html_entity_decode(strip_tags($question->description ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8')), 160))
@section('canonical', route('questions.show', ['question' => $question->slug]))
@section('og-type', 'article')
@section('main-pt', 'pt-8')
@auth('web')
    @section('has-composer', '1')
@endauth

@php
    $url = route('questions.show', ['question' => $question->slug]);
    [$bodyHtml, $inline] = \App\Support\ContentImages::extract($question->description);
    $legacy = $question->photos ? (json_decode($question->photos, true) ?: []) : [];
    $photos = array_merge(array_map(fn ($p) => ['src' => asset('storage/' . $p), 'full' => asset('storage/' . $p)], $legacy), $inline);
    $answers = $question->comments;
    $plain = fn (?string $html) => \App\Support\PlainText::from($html, PHP_INT_MAX);
    // Разметка «вопрос-ответ» для сниппета в выдаче: сам вопрос и ответы участников
    $qaPage = [
        '@context' => 'https://schema.org', '@type' => 'QAPage',
        'mainEntity' => array_filter([
            '@type' => 'Question', 'name' => $question->title, 'text' => $plain($question->description),
            'answerCount' => $answers->count(), 'upvoteCount' => 0, 'dateCreated' => $question->created_at?->toIso8601String(),
            'author' => ['@type' => 'Person', 'name' => optional($question->user)->name ?: 'Посетитель'],
            'acceptedAnswer' => ($accepted = $question->solution()) ? [
                '@type' => 'Answer', 'text' => $plain($accepted->text) ?: 'Ответ с фотографией', 'dateCreated' => $accepted->created_at?->toIso8601String(),
                'url' => $url . '#' . ($question->solution_type === 'comment' ? 'c-' : 'r-') . $accepted->id, 'upvoteCount' => 0,
                'author' => ['@type' => 'Person', 'name' => optional($accepted->user)->name ?: 'Посетитель'],
            ] : null,
            'suggestedAnswer' => $answers->map(fn ($a) => [
                '@type' => 'Answer', 'text' => $plain($a->text) ?: 'Ответ с фотографией', 'dateCreated' => $a->created_at?->toIso8601String(),
                'url' => $url . '#comments', 'upvoteCount' => $a->reactions->where('emoji', '👍')->count(),
                'author' => ['@type' => 'Person', 'name' => optional($a->user)->name ?: 'Посетитель'],
            ])->values()->all() ?: null,
        ], fn ($v) => $v !== null),
    ];
@endphp

@section('hero')
    <x-ui.breadcrumbs :items="[
        ['Вопросы', route('questions.index')],
        [$question->appliance->title, route('public.appliances.show', ['appliance' => $question->appliance->slug])],
        [$question->title, null],
    ]" />
@endsection

@section('sidebar-left')
    @include('public.partials.appliance-nav', ['active' => $question->appliance->slug])
@endsection

@section('content')
    <script type="application/ld+json">@json($qaPage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP)</script>
    <div>
        <article class="overflow-hidden rounded-card bg-white shadow-sm">
            <header class="px-4 pt-3 sm:px-5">
                <div class="mb-2 flex flex-wrap gap-2">
                    <a href="{{ route('public.appliances.show', ['appliance' => $question->appliance->slug]) }}"
                        class="keep rounded bg-brand-400 px-2 py-0.5 text-xs font-bold leading-4 text-black hover:underline">{{ $question->appliance->title }}</a>
                    @if ($question->isSolved())<span class="rounded bg-green-600 px-2 py-0.5 text-xs font-bold leading-4 text-white">✓ Решено</span>@endif
                    @if ($question->model)<span class="rounded border border-line px-2 py-0.5 text-xs leading-4 text-black">{{ $question->model }}</span>@endif
                    @if ($question->error_code)<span class="rounded border border-line px-2 py-0.5 text-xs leading-4 text-black">Ошибка {{ $question->error_code }}</span>@endif
                </div>
                <h1 class="mb-3 text-[22px] font-bold leading-7 text-black sm:text-2xl sm:leading-8">{{ $question->title }}</h1>
            </header>

            <x-ui.photo-grid :photos="$photos" :alt="$question->title" />

            <div class="article-content {{ $bodyHtml === strip_tags($bodyHtml) ? 'whitespace-pre-line' : '' }} px-4 pt-3 text-[15px] leading-[1.5] [overflow-wrap:anywhere] sm:px-5">{!! $bodyHtml !!}</div>

            <footer class="flex flex-wrap items-center justify-end gap-x-3 gap-y-1 px-4 pb-2 pt-1 text-xs leading-5 text-subtle sm:px-5">
                @if (auth('web')->id() === $question->user_id)
                    @if ($question->isSolved())
                        <form method="POST" action="{{ route('questions.solution.destroy', ['id' => $question->id]) }}" class="inline">@csrf @method('DELETE')
                            <button type="submit" class="hover:text-ink">Снять отметку «Решено»</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('questions.solution.store', ['id' => $question->id]) }}" class="inline">@csrf
                            <button type="submit" class="font-medium hover:text-green-700">Отметить решённым</button>
                        </form>
                    @endif
                @endif
                @can('update', $question)
                    <a href="{{ route('questions.edit', ['question' => $question->slug]) }}" class="hover:text-ink">Редактировать</a>
                @endcan
                @can('delete', $question)
                    <form method="POST" action="{{ route('questions.destroy', ['question' => $question->id]) }}"
                        @submit="if (! confirm('Удалить вопрос?')) $event.preventDefault()" x-data class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="hover:text-red-600">Удалить</button>
                    </form>
                @endcan
                <span class="inline-flex items-center gap-1"><x-ui.icon name="eye" class="size-3" />{{ $question->views }}</span>
                <span>{{ optional($question->user)->name }}</span>
                <time datetime="{{ $question->dateAsCarbon->toIso8601String() }}">{{ $question->dateAsCarbon->translatedFormat('j F Y, H:i') }}</time>
            </footer>
        </article>

        @include('public.partials.chat', ['instance' => $question])
    </div>

@endsection

@section('sidebar')
    @include('public.partials.sidebar')
@endsection
