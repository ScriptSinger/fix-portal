@extends('public.layouts.bar')
@section('title', 'О нас | ' . config('app.name', 'Ufamasters'))
@section('page-title')
<div class="page-title db">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-14 col-xs-14">
                <h2>О нас</h2>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-14 hidden-xs-down hidden-sm-down">

            </div>
        </div>
    </div>
</div>
@endsection

@section('sidebar')
<div class="sidebar">
    @include('public.layouts.widgets.sidebar.prime_posts')
    @include('public.layouts.widgets.sidebar.advertising')
    @include('public.layouts.widgets.sidebar.prime_categories')
</div>
@endsection

@section('content')
<div class="page-wrapper">
    <div class="custombox authorbox clearfix">
        <p>Добро пожаловать на платформу, где пользователи получают ресурсы, информацию и советы для изучения устройств</p>

        <h4>Наша миссия</h4>
        <p>Главная цель — дать людям право ремонтировать технику, чтобы продлить срок службы, а значит снизить углеродный след.</p>

        <h4>Что доступно пользователю</h4>
        <p><strong>Статьи</strong>, которые помогают ремонтировать, — информативные материалы на основе опыта и достоверных источников.</p>
        <p><strong>Вопросы и ответы:</strong> получайте помощь и рекомендации сообщества, обменивайтесь знаниями и опытом.</p>
        <p><strong>База прошивок:</strong> нашим пользователям доступно ПО для электронных плат</p>
        <p><strong>Раздел мастеров</strong> здесь мастера предлагают услуги, а пользователи выбирают подходящего ремонтника без посредников и комиссий.</p>

        <div class="contact-info">
            <h4>Обратная связь</h4>
            <p>Для вопросов или предложений по работе сайта: <a
                    href="mailto:ufamasters102@gmail.com">ufamasters102@gmail.com</a>.</p>
            <h4>Обмен опытом</h4>
            <p>Наше<a href="https://chat.whatsapp.com/GbiHdEKIArIBcnhALEVbdu">WhatsApp-сообщество</a>, где участники делятся опытом и поддерживают друг друга — присоединяйтесь
            </p>
        </div>

        <h4>Поддержите нас</h4>
        <p>Сайт работает на платном сервере, а доход идёт за счёт рекламы. Пожалуйста, отключите блокировщик рекламы, чтобы поддержать ресурс</p>

    </div>
</div>
@endsection