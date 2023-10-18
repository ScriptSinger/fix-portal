<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\MainController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DeletedUserController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Public\CategoryController as PublicCategoryController;
use App\Http\Controllers\Public\PostController as PublicPostController;
use App\Http\Controllers\Public\TagController as PublicTagController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [PublicPostController::class, 'index'])->name('home');
Route::get('/article/{slug}', [PublicPostController::class, 'show'])->name('posts.single');
Route::get('/category/{slug}', [PublicCategoryController::class, 'showCategoryArticles'])->name('category.articles');
Route::get('/tag/{slug}', [PublicTagController::class, 'showTagArticles'])->name('tag.articles');
Route::get('/search', [SearchController::class, 'index'])->name('search');


Route::group(
    ['middleware' => 'admin', 'prefix' => 'admin'],
    function () {
        Route::get('/', [MainController::class, 'index'])->name('admin.index');
        Route::resource('tasks', TaskController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('tags', TagController::class);
        Route::resource('posts', PostController::class);
        Route::resource('users', AdminUserController::class);
        Route::get('/deleted-users', [DeletedUserController::class, 'index'])->name('deleted-users.index');
        Route::get('/deleted-users/restore/{id}', [DeletedUserController::class, 'restore'])->name('deleted-users.restore');
    }
);

// Route::post('/logout', [UserController::class, 'logout'])->name('logout')->middleware('auth');

// Route::group(
//     ['middleware' => 'guest'],
//     function () {
//         Route::get('/register', [UserController::class, 'showRegistrationForm'])->name('register.form');
//         Route::post('/register', [UserController::class, 'register'])->name('register.store');
//         Route::get('/login', [UserController::class, 'login'])->name('login');
//         Route::get('/login/google', [UserController::class, 'redirectToGoogle'])->name('login.google');
//         Route::get('/login/google/callback', [UserController::class, 'handleGoogleCallback']);
//         Route::post('/login', [UserController::class, 'authenticate'])->name('auth');
//     }
// );

require __DIR__ . '/auth.php';
