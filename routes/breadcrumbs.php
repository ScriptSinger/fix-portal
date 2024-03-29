<?php

use App\Models\Appliance;
use App\Models\Category;
use App\Models\Firmware;
use App\Models\Post;
use App\Models\Question;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Str;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Home
Breadcrumbs::for('home', function (BreadcrumbTrail $trail) {
    $trail->push('Главная', route('articles.index'));
});

// Категория
Breadcrumbs::for('category', function (BreadcrumbTrail $trail, Category $category) {
    $trail->parent('home');
    $trail->push($category->title, route('categories.show', $category->slug));
});

Breadcrumbs::for('tag', function (BreadcrumbTrail $trail, Tag $tag) {
    $trail->parent('home');
    $trail->push($tag->title, route('categories.show', $tag->slug));
});

// Post
Breadcrumbs::for('post', function (BreadcrumbTrail $trail, Category $category, Post $post) {
    $trail->parent('category', $category);
    $trail->push($post->title, route('articles.show', $post));
});

// Вопросы
Breadcrumbs::for('questions', function (BreadcrumbTrail $trail) {
    $trail->push('Вопросы', route('questions.index'));
});

// Приборы
Breadcrumbs::for('appliance', function (BreadcrumbTrail $trail, Appliance $appliance) {
    $trail->parent('questions');
    $trail->push($appliance->title, route('public.appliances.show', $appliance->slug));
});

// Question
Breadcrumbs::for('question', function (BreadcrumbTrail $trail, Appliance $appliance, Question $question) {
    $trail->parent('appliance', $appliance);
    $trail->push($question->title, route('questions.show', $question));
});

Breadcrumbs::for('question-create', function (BreadcrumbTrail $trail) {
    $trail->parent('questions');
    $trail->push('Создать вопрос', route('questions.create'));
});


// Firmwares
Breadcrumbs::for('firmwares', function (BreadcrumbTrail $trail) {
    $trail->push('Прошивки', route('firmwares.index'));
});

Breadcrumbs::for('firmware', function (BreadcrumbTrail $trail, Firmware $firmware) {
    $trail->parent('firmwares');
    $trail->push(Str::limit($firmware->title, 30, '...'), route('firmwares.show', $firmware));
});

// Личный кабинет
Breadcrumbs::for('profile', function (BreadcrumbTrail $trail) {
    $trail->push('Личный кабинет', route('profile.edit'));
});

// Users
Breadcrumbs::for('users', function (BreadcrumbTrail $trail) {
    $trail->push('Мастера', route('users.index'));
});

Breadcrumbs::for('user', function (BreadcrumbTrail $trail, User $user) {
    $trail->parent('users');
    $trail->push($user->name, route('users.show', $user));
});
