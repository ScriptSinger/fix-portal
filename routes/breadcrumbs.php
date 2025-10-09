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

Breadcrumbs::for('home', function (BreadcrumbTrail $trail) {
    $trail->push('Главная', route('articles.index'));
});

Breadcrumbs::for('categories', function (BreadcrumbTrail $trail) {
    $trail->parent('home');
    $trail->push('Категории', route('categories.index'));
});

Breadcrumbs::for('category', function (BreadcrumbTrail $trail, Category $category) {
    $trail->parent('home');
    $trail->push('Категории', route('categories.index'));
    $trail->push($category->title, route('categories.show', $category->slug));
});

Breadcrumbs::for('tags', function (BreadcrumbTrail $trail) {
    $trail->parent('home');
    $trail->push('Теги', route('tags.index'));
});

Breadcrumbs::for('tag', function (BreadcrumbTrail $trail, Tag $tag) {
    $trail->parent('home');
    $trail->push('Теги', route('tags.index'));
    $trail->push($tag->title, route('tags.show', $tag->slug));
});

Breadcrumbs::for('post', function (BreadcrumbTrail $trail, Category $category, Post $post) {
    $trail->parent('category', $category);
});

Breadcrumbs::for('questions', function (BreadcrumbTrail $trail) {
    $trail->push('Вопросы', route('questions.index'));
});

Breadcrumbs::for('appliance', function (BreadcrumbTrail $trail, Appliance $appliance) {
    $trail->parent('questions');
    $trail->push($appliance->title, route('public.appliances.show', $appliance->slug));
});

Breadcrumbs::for('question', function (BreadcrumbTrail $trail, Appliance $appliance, Question $question) {
    $trail->parent('appliance', $appliance);
    $trail->push($question->title, route('questions.show', $question));
});

Breadcrumbs::for('question-create', function (BreadcrumbTrail $trail) {
    $trail->parent('questions');
    $trail->push('Создать вопрос', route('questions.create'));
});

Breadcrumbs::for('firmwares', function (BreadcrumbTrail $trail) {
    $trail->push('Прошивки', route('firmwares.index'));
});

Breadcrumbs::for('firmware', function (BreadcrumbTrail $trail, Firmware $firmware) {
    $trail->parent('firmwares');
    $trail->push(Str::limit($firmware->title, 30, '...'), route('firmwares.show', $firmware));
});

Breadcrumbs::for('profile', function (BreadcrumbTrail $trail) {
    $trail->push('Личный кабинет', route('profile.edit'));
});

Breadcrumbs::for('users', function (BreadcrumbTrail $trail) {
    $trail->push('Мастера', route('users.index'));
});

Breadcrumbs::for('user', function (BreadcrumbTrail $trail, User $user) {
    $trail->parent('users');
    $trail->push($user->name, route('users.show', $user));
});
