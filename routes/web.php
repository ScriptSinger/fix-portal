<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Public\FirmwareController;
use App\Http\Controllers\Public\FirmwareHubController;
use App\Services\FirmwareCatalog;
use App\Http\Controllers\Public\QuestionController;
use App\Http\Controllers\Public\ApplianceController;
use App\Http\Controllers\Public\CategoryController;
use App\Http\Controllers\Public\CommentController;
use App\Http\Controllers\Public\PostController;
use App\Http\Controllers\Public\ReplyController;
use App\Http\Controllers\Public\TagController;
use App\Http\Controllers\Public\UserController;

// use function Laravel\Prompts\search;

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
    return redirect('/articles');
});

Route::view('/about', 'public.about.show')->name('about');

Route::group(
    ['prefix' => 'articles'],
    function () {
        Route::get('/', [PostController::class, 'index'])->name('articles.index');
        Route::get('/{article}', [PostController::class, 'show'])->name('articles.show');
    }
);

// Пилот перехода на Vite + Tailwind: та же логика, другой шаблон, закрыто от индексации.
Route::get('/pilot/articles', [PostController::class, 'index'])->name('pilot.articles');
Route::get('/pilot/articles/{article}', [PostController::class, 'show'])->name('pilot.articles.show');
Route::get('/pilot/categories', [CategoryController::class, 'index'])->name('pilot.categories.index');
Route::get('/pilot/categories/{category}', [CategoryController::class, 'show'])->name('pilot.categories.show');
Route::view('/pilot/consent', 'tw.pages.consent')->name('pilot.consent');
Route::view('/pilot/about', 'tw.pages.about')->name('pilot.about');
Route::get('/pilot/appliances/{appliance}', [ApplianceController::class, 'show'])->name('pilot.appliances.show');
Route::get('/pilot/questions', [QuestionController::class, 'index'])->name('pilot.questions.index');
Route::middleware(['auth:web', 'verified'])->group(function () {
    Route::get('/pilot/questions/create', [QuestionController::class, 'create'])->name('pilot.questions.create');
    Route::get('/pilot/questions/{question}/edit', [QuestionController::class, 'edit'])->name('pilot.questions.edit');
});
Route::get('/pilot/questions/{question}', [QuestionController::class, 'show'])->name('pilot.questions.show');
Route::middleware('guest')->group(function () {
    Route::get('/pilot/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])->name('pilot.login');
    Route::get('/pilot/register', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])->name('pilot.register');
    Route::get('/pilot/forgot-password', [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'create'])->name('pilot.password.request');
    Route::get('/pilot/reset-password/{token}', [\App\Http\Controllers\Auth\NewPasswordController::class, 'create'])->name('pilot.password.reset');
});
Route::get('/pilot/verify-email', \App\Http\Controllers\Auth\EmailVerificationPromptController::class)->middleware('auth')->name('pilot.verification.notice');
Route::get('/pilot/profile/edit', [UserController::class, 'edit'])->middleware(['auth:web', 'verified'])->name('pilot.profile.edit');
Route::get('/pilot/tags', [TagController::class, 'index'])->name('pilot.tags.index');
Route::get('/pilot/tag/{tag}', [TagController::class, 'show'])->name('pilot.tags.show');

Route::redirect('/article/{slug}', '/articles/{slug}', 301);


// Route::get('/users', [UserController::class, 'index'])->name('users.index');
// Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');

Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
Route::get('/tag/{tag}', [TagController::class, 'show'])->name('tags.show');

Route::resource('questions', QuestionController::class);

Route::get('/appliances/{appliance}', [ApplianceController::class, 'show'])->name('public.appliances.show');

Route::get('/firmwares', [FirmwareController::class, 'index'])->name('firmwares.index');

// Страницы-хабы каталога: /firmwares/indesit и /firmwares/indesit/wisl. Список марок берётся из правил
// config/firmware_brands.php, поэтому адреса не пересекаются с /firmwares/285 (прошивка по номеру).
$firmwareBrands = implode('|', array_map('preg_quote', array_keys(FirmwareCatalog::brandSlugs())));

Route::get('/firmwares/{brand}', [FirmwareHubController::class, 'brand'])
    ->where('brand', $firmwareBrands)
    ->name('firmwares.brand');
Route::get('/firmwares/{brand}/{series}', [FirmwareHubController::class, 'series'])
    ->where(['brand' => $firmwareBrands, 'series' => '[a-z]+'])
    ->name('firmwares.series');

Route::get('/firmwares/{firmware}', [FirmwareController::class, 'show'])->whereNumber('firmware')->name('firmwares.show');

// Пилот перехода на Vite + Tailwind
Route::get('/pilot/firmwares', [FirmwareController::class, 'index'])->name('pilot.firmwares.index');
Route::get('/pilot/firmwares/{brand}', [FirmwareHubController::class, 'brand'])->where('brand', $firmwareBrands)->name('pilot.firmwares.brand');
Route::get('/pilot/firmwares/{brand}/{series}', [FirmwareHubController::class, 'series'])->where(['brand' => $firmwareBrands, 'series' => '[a-z]+'])->name('pilot.firmwares.series');
Route::get('/pilot/firmwares/{firmware}', [FirmwareController::class, 'show'])->whereNumber('firmware')->name('pilot.firmwares.show');



Route::middleware(['auth:web', 'verified'])->group(
    function () {
        Route::get('/profile/edit', [UserController::class, 'edit'])->name('profile.edit');
        Route::put('/profile/update', [UserController::class, 'update'])->name('profile.update');
        Route::post('/{type}/{id}/comments', [CommentController::class, 'store'])->name('comments.store');
        Route::group(['prefix' => '/comments'], function () {
            Route::put('/{id}', [CommentController::class, 'update'])->name('comments.update');
            Route::delete('/{id}', [CommentController::class, 'destroy'])->name('comments.destroy');
            Route::post('/{id}/replies', [ReplyController::class, 'store'])->name('comments.replies.store');
        });

        Route::put('/replies/{id}', [ReplyController::class, 'update'])->name('comments.replies.update');
        Route::delete('/replies/{id}', [ReplyController::class, 'destroy'])->name('comments.replies.destroy');

        Route::prefix('/questions')->group(function () {
            Route::get('/create', [QuestionController::class, 'create'])->name('questions.create');
            Route::post('/', [QuestionController::class, 'store'])->name('questions.store');
            Route::delete('/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
            // Переопределение методов ресурсного контроллера
        });

        Route::get('/firmwares/{firmware}/download', [FirmwareController::class, 'download'])->name('firmwares.download');
    }
);


require __DIR__ . '/auth.php';
