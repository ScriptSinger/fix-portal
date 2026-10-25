@props(['user', 'size' => 'size-10'])
@php $uri = optional($user->avatar)->uri; @endphp
@if ($uri)
    <img src="{{ Storage::url($uri) }}" alt="{{ $user->name }}" loading="lazy"
        class="{{ $size }} shrink-0 rounded-full object-cover">
@else
    <span class="{{ $size }} inline-flex shrink-0 items-center justify-center rounded-full bg-brand-400 font-bold text-ink"
        aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
@endif
