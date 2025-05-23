<?php

use App\Http\Controllers\Api\Admin\BlockedController;
use App\Http\Controllers\Api\Admin\BlockedUserAgentController;
use App\Http\Controllers\Api\Admin\PostImageController;
use App\Http\Controllers\Api\ApplianceController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\FirmwareController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\Public\AvatarController;
use App\Http\Controllers\Api\Public\MasterController;
use App\Http\Controllers\Api\Public\UserImageController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\ReplyController;
use App\Http\Controllers\Api\RequestLogController;
use App\Http\Controllers\Api\StatisticController;
use App\Http\Controllers\Api\TagController;

use App\Http\Controllers\Api\UserController;

use App\Http\Controllers\Public\LikeController;
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


        Route::delete('/firmwares/{firmware}', [FirmwareController::class, 'destroy'])->name('api.firmwares.destroy');
        Route::put('/firmwares/{firmware}', [FirmwareController::class, 'restore'])->name('api.firmwares.restore');

        Route::get('/users', [UserController::class, 'index'])->name('api.users.index');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('api.users.destroy');
        Route::put('/users/{user}', [UserController::class, 'restore'])->name('api.users.restore');

        Route::get('/logs', [RequestLogController::class, 'index'])->name('api.logs.index');

        Route::get('/comments', [CommentController::class, 'index'])->name('api.comments.index');

        Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('api.comments.destroy');
        Route::put('/comments/{comment}', [CommentController::class, 'restore'])->name('api.comments.restore');

        Route::get('/replies/{comment?}', [ReplyController::class, 'index'])->name('api.replies.index');
        Route::delete('/replies/{reply}', [ReplyController::class, 'destroy'])->name('api.replies.destroy');
        Route::put('/replies/{reply}', [ReplyController::class, 'restore'])->name('api.replies.restore');

        Route::get('/questions', [QuestionController::class, 'index'])->name('api.questions.index');
        Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('api.questions.destroy');
        Route::put('/questions/{question}', [QuestionController::class, 'restore'])->name('api.questions.restore');



        Route::get('/post-images', [PostImageController::class, 'index'])->name('api.post.images.index');
        Route::get('/post-images/{image}', [PostImageController::class, 'show'])->name('api.post.images.show');
        Route::post('/post-images', [PostImageController::class, 'upload'])->name('api.post.images.upload');
        Route::delete('/post-images/{image}', [PostImageController::class, 'destroy'])->name('api.post.images.destroy');

        Route::get('/statistics', [StatisticController::class, 'index'])->name('api.statistics.index');

        Route::get('/blockeds', [BlockedController::class, 'index'])->name('api.blockeds.index');
        Route::delete('/blockeds/{blocked}', [BlockedController::class, 'destroy'])->name('api.blockeds.destroy');
        Route::put('/blockeds/{blocked}', [BlockedController::class, 'restore'])->name('api.blockeds.restore');

        Route::get('/blocked-agents', [BlockedUserAgentController::class, 'index'])->name('api.agents.index');
        Route::delete('/agents/{agent}', [BlockedUserAgentController::class, 'destroy'])->name('api.agents.destroy');
        Route::put('/agents/{agent}', [BlockedUserAgentController::class, 'restore'])->name('api.agents.restore');
    }
);

Route::get('/firmwares', [FirmwareController::class, 'index'])->name('api.firmwares.index');
Route::get('/masters', [MasterController::class, 'index'])->name('api.masters.index');

Route::group(
    [
        'middleware' => 'auth:web,admin',
    ],
    function () {
        Route::prefix('/{type}/{id}/')->group(function () {
            Route::post('/like', [LikeController::class, 'like'])->name('like');
            Route::post('/dislike', [LikeController::class, 'dislike'])->name('dislike');
        });

        Route::get('users-images', [UserImageController::class, 'index'])->name('api.users.images.index');
        Route::post('users-images', [UserImageController::class, 'upload'])->name('api.users.images.upload');
        Route::delete('users-images/{image}', [UserImageController::class, 'destroy'])->name('api.users.images.destroy');

        Route::get('/avatars', [AvatarController::class, 'show'])->name('api.avatars.show');
        Route::post('/avatars', [AvatarController::class, 'upload'])->name('api.avatars.upload');
        Route::delete('/avatars', [AvatarController::class, 'destroy'])->name('api.avatars.destroy');
    }
);
