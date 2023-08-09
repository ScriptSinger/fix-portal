<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\MainController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\TagController;
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

Route::get('/', function () {
    return view('welcome');
});

Route::group(
    ['middleware' => 'admin', 'prefix' => 'admin'],
    function () {
        Route::get('/', [MainController::class, 'index'])->name('admin.index');
        Route::resource('tasks', TaskController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('tags', TagController::class);
    }
);

Route::post('/logout', [UserController::class, 'logout'])->name('logout')->middleware('auth');

Route::group(
    ['middleware' => 'guest'],
    function () {
        Route::get('/register', [UserController::class, 'showRegistrationForm'])->name('register.form');
        Route::post('/register', [UserController::class, 'register'])->name('register.store');
        Route::get('/login', [UserController::class, 'login'])->name('login');
        Route::get('/login/google', [UserController::class, 'redirectToGoogle'])->name('login.google');
        Route::get('/login/google/callback', [UserController::class, 'handleGoogleCallback']);


        Route::post('/login', [UserController::class, 'authenticate'])->name('auth');
    }
);
