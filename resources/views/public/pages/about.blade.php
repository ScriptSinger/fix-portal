@extends('public.layouts.app')
@section('title', 'О нас | ' . config('app.name', 'Ufamasters'))
@section('description', 'О нас: платформа с статьями, вопросами и ответами, базой прошивок и каталогом мастеров по ремонту бытовой техники.')

@section('hero')
    <x-ui.page-header title="О нас" />
@endsection

@section('content')
    <div class="rounded-[5px] bg-white p-12 max-sm:px-4">
        <div class="article-content border border-dashed border-dashed px-8 py-12 max-sm:px-4">
            <p>Добро пожаловать на платформу, где пользователи получают ресурсы, информацию и советы для изучения устройств</p>
            <h2 class="!mb-[14px] !text-[19px] !leading-[29px]">Наша миссия</h2>
            <p>Главная цель — дать людям право ремонтировать технику, чтобы продлить срок службы, а значит снизить углеродный след.</p>
            <h2 class="!mb-[14px] !text-[19px] !leading-[29px]">Что доступно пользователю</h2>
            <p><strong>Статьи</strong>, которые помогают ремонтировать, — информативные материалы на основе опыта и достоверных источников.</p>
            <p><strong>Вопросы и ответы:</strong> получайте помощь и рекомендации сообщества, обменивайтесь знаниями и опытом.</p>
            <p><strong>База прошивок:</strong> нашим пользователям доступно ПО для электронных плат</p>
            <p><strong>Раздел мастеров</strong> здесь мастера предлагают услуги, а пользователи выбирают подходящего ремонтника без посредников и комиссий.</p>
            <h2 class="!mb-[14px] !text-[19px] !leading-[29px]">Обратная связь</h2>
            <p>Для вопросов или предложений по работе сайта: <a href="mailto:ufamasters102@gmail.com">ufamasters102@gmail.com</a>.</p>
            <h2 class="!mb-[14px] !text-[19px] !leading-[29px]">Обмен опытом</h2>
            <p>Наше <a href="https://chat.whatsapp.com/GbiHdEKIArIBcnhALEVbdu">WhatsApp-сообщество</a>, где участники делятся опытом и поддерживают друг друга — присоединяйтесь</p>
            <h2 class="!mb-[14px] !text-[19px] !leading-[29px]">Поддержите нас</h2>
            <p>Сайт работает на платном сервере, а доход идёт за счёт рекламы. Пожалуйста, отключите блокировщик рекламы, чтобы поддержать ресурс</p>
        </div>
    </div>
@endsection

@section('sidebar')
    @include('public.partials.sidebar')
@endsection
