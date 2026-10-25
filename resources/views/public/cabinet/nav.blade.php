@props(['active' => 'index'])
{{-- Меню личного кабинета; на телефоне выезжает слева, как остальные разделы --}}
@php $unread = auth()->user()->unreadNotifications()->count(); @endphp
<x-ui.side-nav heading="Кабинет" label="Личный кабинет" :items="[
    ['title' => 'Обзор', 'url' => route('cabinet.index'), 'active' => $active === 'index'],
    ['title' => 'Уведомления', 'url' => route('cabinet.notifications'), 'active' => $active === 'notifications', 'badge' => $unread ?: null],
    ['title' => 'Мои вопросы', 'url' => route('cabinet.questions'), 'active' => $active === 'questions'],
    ['title' => 'Мои ответы', 'url' => route('cabinet.answers'), 'active' => $active === 'answers'],
    ['title' => 'Профиль', 'url' => route('profile.edit'), 'active' => $active === 'profile'],
]" />
