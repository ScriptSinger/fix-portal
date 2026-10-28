{{-- Фильтр списка вопросов: все, решённые, без решения (только вопросы, созданные после запуска отметок), без ответов (любой давности) --}}
@php
    $current = in_array(request('status'), ['solved', 'open', 'unanswered'], true) ? request('status') : null;
    $tabs = [null => 'Все', 'solved' => 'Решённые', 'open' => 'Без решения', 'unanswered' => 'Без ответов'];
    $base = request()->except(['status', 'page']);
@endphp
<nav aria-label="Статус вопросов" class="mb-4 flex flex-wrap gap-2">
    @foreach ($tabs as $key => $label)
        <a href="{{ url()->current() . ($base || $key ? '?' . http_build_query($base + ($key ? ['status' => $key] : [])) : '') }}" @if ($current === $key) aria-current="true" @endif
            class="keep inline-flex h-9 items-center rounded-full px-4 text-sm font-medium {{ $current === $key ? 'bg-black text-brand-400' : 'bg-white text-ink shadow-sm' }}">{{ $label }}</a>
    @endforeach
</nav>
