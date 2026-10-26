@props(['user', 'size' => 'size-16'])
@php $uri = optional($user->avatar)->uri; @endphp
<img src="{{ $uri ? Storage::url($uri) : asset('assets/front/images/avatar.png') }}" alt="{{ $user->name }}" loading="lazy"
    class="{{ $size }} shrink-0 rounded-full object-cover">
