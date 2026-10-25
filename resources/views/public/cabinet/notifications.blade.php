@extends('public.layouts.app')
@section('title', 'Уведомления | ' . config('app.name', 'Ufamasters'))
@section('robots', 'noindex, nofollow')
@section('wide', '1')

@section('hero')
    <x-ui.page-header title="Уведомления" :items="[['Личный кабинет', route('cabinet.index')], ['Уведомления', null]]" />
@endsection

@section('sidebar')
    @include('public.cabinet.nav', ['active' => 'notifications'])
@endsection

@section('content')
    @php
        // реакции одного человека-эмодзи на одно сообщение склеиваем: «Сергей и ещё 2»
        $groups = $notifications->groupBy(fn ($n) => $n->created_at->toDateString());
        $actors = \App\Models\User::with('avatar')->whereIn('id', $notifications->map(fn ($n) => $n->data['actor_id'] ?? null)->filter()->unique())->get()->keyBy('id');
        $unread = auth()->user()->unreadNotifications()->count();
        $tab = 'inline-flex h-9 items-center rounded-full px-4 text-sm font-medium ';
    @endphp
    <div class="mb-3 flex flex-wrap items-center gap-2">
        <a href="{{ route('cabinet.notifications') }}" class="keep {{ $tab }} {{ $only ? 'bg-black/5 text-ink' : 'bg-black text-brand-400' }}">Все</a>
        <a href="{{ route('cabinet.notifications', ['show' => 'new']) }}" class="keep {{ $tab }} {{ $only ? 'bg-black text-brand-400' : 'bg-black/5 text-ink' }}">Новые · {{ $unread }}</a>
        @if ($unread)
            <form method="POST" action="{{ route('cabinet.notifications.read-all') }}" class="ml-auto">@csrf
                <button type="submit" class="text-sm font-bold text-black underline">Прочитать все</button>
            </form>
        @endif
    </div>

    @forelse ($groups as $date => $items)
        @php $day = \Illuminate\Support\Carbon::parse($date); @endphp
        <div class="mx-auto my-4 w-max rounded-full bg-black/5 px-3 py-0.5 text-xs font-medium text-subtle">
            {{ $day->isToday() ? 'Сегодня' : ($day->isYesterday() ? 'Вчера' : $day->translatedFormat('j F Y')) }}
        </div>
        @php
            $merged = $items->groupBy(fn ($n) => ($n->data['kind'] ?? '') === 'reaction' ? 'reaction|' . ($n->data['target_id'] ?? '') . '|' . ($n->data['emoji'] ?? '') : $n->id);
        @endphp
        @foreach ($merged as $set)
            @php
                $n = $set->first(); $d = $n->data; $actor = $actors->get($d['actor_id'] ?? 0); $isNew = $set->contains(fn ($x) => $x->read_at === null);
                $others = $set->count() - 1;
                $who = '<b>' . e($d['actor_name'] ?? 'Участник') . '</b>' . ($others > 0 ? ' и ещё ' . $others . ' ' . trans_choice('человек|человека|человек', $others, [], 'ru') : '');
                $title = '<b>«' . e(\Illuminate\Support\Str::limit($d['question_title'] ?? '', 70)) . '»</b>';
                $text = match ($d['kind'] ?? '') {
                    'answer' => $who . ' ответил на ваш вопрос ' . $title,
                    'reply' => $who . ' ответил вам в вопросе ' . $title,
                    'reaction' => $who . ' поставил реакцию <span class="emoji">' . e($d['emoji'] ?? '') . '</span> на ваше сообщение в вопросе ' . $title,
                    default => $who . ' ' . $title,
                };
            @endphp
            <a href="{{ route('cabinet.notifications.open', ['id' => $n->id]) }}" class="keep relative mb-2 grid grid-cols-[2.5rem_minmax(0,1fr)] gap-3 rounded-2xl bg-white py-3 pl-3.5 pr-4 shadow-sm">
                @if ($actor)
                    <x-ui.avatar :user="$actor" size="size-10" />
                @else
                    <span class="grid size-10 place-items-center rounded-full bg-black font-bold text-brand-400">U</span>
                @endif
                <div class="min-w-0">
                    <div class="pr-5 text-sm leading-[1.45] text-ink">{!! $text !!}</div>
                    @if (! empty($d['snippet']))
                        <div class="my-1 rounded-r-md border-l-[3px] border-subtle bg-black/5 px-2.5 py-0.5 text-[13px] text-ink/80"><span class="line-clamp-2">{{ $d['snippet'] }}</span></div>
                    @endif
                    <div class="mt-1 text-xs text-subtle">{{ $n->created_at->format('H:i') }}</div>
                </div>
                @if ($isNew)<span class="absolute right-3.5 top-4 size-2.5 rounded-full bg-red-600" aria-label="Новое"></span>@endif
            </a>
        @endforeach
    @empty
        <p class="rounded-2xl bg-white p-5 text-subtle shadow-sm">{{ $only ? 'Новых уведомлений нет.' : 'Пока нет уведомлений. Здесь появятся ответы на ваши вопросы и реакции на сообщения.' }}</p>
    @endforelse

    <div class="mt-6">{{ $notifications->links('public.partials.pagination') }}</div>
@endsection
