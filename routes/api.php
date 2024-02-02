<?php

use App\Http\Controllers\Api\ApplianceController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\FirmwareController;

use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\SummernoteController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\UserController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(
    ['middleware' => 'auth:admin', 'prefix' => 'heturion'],
    function () {
        Route::get('/categories', [CategoryController::class, 'index'])->name('api.categories.index');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('api.categories.destroy');
        Route::put('/categories/{category}', [CategoryController::class, 'restore'])->name('api.categories.restore');

        Route::get('/tags', [TagController::class, 'index'])->name('api.tags.index');
        Route::delete('/tags/{tag}', [TagController::class, 'destroy'])->name('api.tags.destroy');
        Route::put('/tags/{tag}', [TagController::class, 'restore'])->name('api.tags.restore');

        Route::get('/appliances', [ApplianceController::class, 'index'])->name('api.appliances.index');
        Route::delete('/appliances/{appliance}', [ApplianceController::class, 'destroy'])->name('api.appliances.destroy');
        Route::put('/appliances/{appliance}', [ApplianceController::class, 'restore'])->name('api.appliances.restore');


        Route::get('/posts', [PostController::class, 'index'])->name('api.posts.index');
        Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('api.posts.destroy');
        Route::put('/posts/{post}', [PostController::class, 'restore'])->name('api.posts.restore');

        Route::get('/firmwares', [FirmwareController::class, 'index'])->name('api.firmwares.index');
        Route::delete('/firmwares/{firmware}', [FirmwareController::class, 'destroy'])->name('api.firmwares.destroy');
        Route::put('/firmwares/{firmware}', [FirmwareController::class, 'restore'])->name('api.firmwares.restore');

        Route::get('/users', [UserController::class, 'index'])->name('api.users.index');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('api.users.destroy');
        Route::put('/users/{user}', [UserController::class, 'restore'])->name('api.users.restore');

        Route::get('/comments', [CommentController::class, 'index'])->name('api.comments.index');
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('api.comments.destroy');
        Route::put('/comments/{comment}', [CommentController::class, 'restore'])->name('api.comments.restore');

        Route::get('/questions', [QuestionController::class, 'index'])->name('api.questions.index');
        Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('api.questions.destroy');
        Route::put('/questions/{question}', [QuestionController::class, 'restore'])->name('api.questions.restore');


        Route::post('/summernote/upload', [SummernoteController::class, 'upload'])->name('api.summernote.upload');
        Route::delete('/summernote/destroy', [SummernoteController::class, 'destroy'])->name('api.summernote.destroy');
    }
);
