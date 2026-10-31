@props(['user', 'size' => 'size-16', 'profile' => false])
@php $uri = optional($user->avatar)->uri; @endphp
@if ($profile)
    <button type="button"
        data-user-profile-id="{{ $user->id }}"
        data-user-profile-url="{{ route('users.profile', ['user' => $user->id]) }}"
        class="group/profile relative shrink-0 rounded-full focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
        aria-label="Открыть профиль: {{ $user->name }}">
        <img src="{{ $uri ? Storage::url($uri) : asset('assets/front/images/avatar.png') }}" alt="{{ $user->name }}" loading="lazy"
            class="{{ $size }} rounded-full object-cover transition group-hover/profile:brightness-90">
    </button>
@else
    <img src="{{ $uri ? Storage::url($uri) : asset('assets/front/images/avatar.png') }}" alt="{{ $user->name }}" loading="lazy"
        class="{{ $size }} shrink-0 rounded-full object-cover">
@endif
