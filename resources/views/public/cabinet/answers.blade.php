@extends('public.layouts.app')
@section('title', 'Мои ответы | ' . config('app.name', 'Ufamasters'))
@section('robots', 'noindex, nofollow')
@section('wide', '1')

@section('hero')
    <x-ui.page-header title="Мои ответы" :items="[['Личный кабинет', route('cabinet.index')], ['Мои ответы', null]]" />
@endsection

@section('sidebar')
    @include('public.cabinet.nav', ['active' => 'answers'])
@endsection

@section('content')
    <section class="rounded-2xl bg-white px-5 py-2 shadow-sm">
        @forelse ($answers as $answer)
            @php
                $plain = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $answer['text']), ENT_QUOTES | ENT_HTML5, 'UTF-8'))), 160) ?: 'Фото';
                $counts = $answer['reactions']->groupBy('emoji')->map->count();
            @endphp
            <div class="flex items-start gap-3 border-b border-line py-3.5 last:border-0">
                <div class="min-w-0 flex-1">
                    <a href="{{ route('questions.show', ['question' => $answer['question']->slug]) }}#{{ $answer['anchor'] }}" class="keep text-black hover:underline">«{{ $plain }}»</a>
                    <div class="text-[13px] text-subtle">в вопросе {{ $answer['question']->title }} · {{ $answer['at']->diffForHumans() }}</div>
                </div>
                @if ($counts->isNotEmpty())
                    <span class="emoji shrink-0 rounded-full bg-black/5 px-2.5 py-0.5 text-xs">@foreach ($counts as $emoji => $n){{ $emoji }} {{ $n }} @endforeach</span>
                @endif
            </div>
        @empty
            <p class="py-4 text-subtle">Вы ещё не отвечали на вопросы.</p>
        @endforelse
    </section>
@endsection
