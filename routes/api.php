<?php

use App\Http\Controllers\Api\ApplianceController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\FirmwareController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\QuestionController;
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
        Route::get('/categories', [CategoryController::class, 'index'])->name('api.categories.edit');
        Route::get('/tags', [TagController::class, 'index']);
        Route::get('/appliances', [ApplianceController::class, 'index']);
        Route::get('/posts', [PostController::class, 'index']);
        Route::get('/firmwares', [FirmwareController::class, 'index']);

        Route::get('/users', [UserController::class, 'index']);


        Route::get('/comments', [CommentController::class, 'index']);
        Route::get('/questions', [QuestionController::class, 'index']);

        Route::get('{id}/comments', [UserController::class, 'comments']);
    }
);
