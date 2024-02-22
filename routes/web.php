<?php

use App\Http\Controllers\Admin\ApplianceController as AdminApplianceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MainController;
use App\Http\Controllers\Admin\DeletedUserController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\AuthSessionController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Admin\CustomizationController;
use App\Http\Controllers\Public\FirmwareController;
use App\Http\Controllers\Admin\FirmwareController as AdminFirmwareController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Public\QuestionController;
use App\Http\Controllers\Admin\QuestionController as AdminQuestionController;
use App\Http\Controllers\Admin\ReplyController as AdminReplyController;
use App\Http\Controllers\Admin\RequestLogController;
use App\Http\Controllers\Admin\TagController as AdminTagController;
use App\Http\Controllers\Public\ApplianceController;
use App\Http\Controllers\Public\CategoryController;
use App\Http\Controllers\Public\CommentController;
use App\Http\Controllers\Public\LikeController;
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

Route::group(
    ['prefix' => 'articles'],
    function () {
        Route::get('/', [PostController::class, 'index'])->name('articles.index');
        Route::get('/{article}', [PostController::class, 'show'])->name('articles.show');
    }
);

Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');

Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/tag/{slug}', [TagController::class, 'showTagArticles'])->name('tag.articles');

Route::resource('questions', QuestionController::class);

Route::get('/appliances/{appliance}', [ApplianceController::class, 'show'])->name('public.appliances.show');

Route::get('/firmwares', [FirmwareController::class, 'index'])->name('firmwares.index');
Route::get('/firmwares/{firmware}', [FirmwareController::class, 'show'])->name('firmwares.show');


Route::group(
    ['middleware' => ['auth:web', 'verified']],
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
            Route::delete('/{id}', [QuestionController::class, 'destroy'])->name('questions.destroy');
            // Переопределение методов ресурсного контроллера
        });


        Route::get('/firmwares/download/{filename}', [FirmwareController::class, 'download'])->name('firmwares.download');
    }
);


Route::group(
    ['middleware' => 'auth:admin', 'prefix' => 'heturion'],
    function () {
        Route::get('/', [MainController::class, 'index'])->name('admin.index');

        Route::resource('categories', AdminCategoryController::class)
            ->names([
                'index'   => 'admin.categories.index',
                'create'  => 'admin.categories.create',
                'store'   => 'admin.categories.store',
                'show'    => 'admin.categories.show',
                'edit'    => 'admin.categories.edit',
                'update'  => 'admin.categories.update',
                'destroy' => 'admin.categories.destroy',
            ]);

        Route::resource('tags', AdminTagController::class)
            ->names([
                'index'   => 'admin.tags.index',
                'create'  => 'admin.tags.create',
                'store'   => 'admin.tags.store',
                'show'    => 'admin.tags.show',
                'edit'    => 'admin.tags.edit',
                'update'  => 'admin.tags.update',
                'destroy' => 'admin.tags.destroy',
            ]);

        Route::resource('posts', AdminPostController::class)
            ->names([
                'index'   => 'admin.posts.index',
                'create'  => 'admin.posts.create',
                'store'   => 'admin.posts.store',
                'show'    => 'admin.posts.show',
                'edit'    => 'admin.posts.edit',
                'update'  => 'admin.posts.update',
                'destroy' => 'admin.posts.destroy',
            ]);

        Route::resource('users', AdminUserController::class)
            ->names([
                'index'   => 'admin.users.index',
                'create'  => 'admin.users.create',
                'store'   => 'admin.users.store',
                'show'    => 'admin.users.show',
                'edit'    => 'admin.users.edit',
                'update'  => 'admin.users.update',
                'destroy' => 'admin.users.destroy',
            ]);

        Route::resource('appliances', AdminApplianceController::class)
            ->names([
                'index'   => 'admin.appliances.index',
                'create'  => 'admin.appliances.create',
                'store'   => 'admin.appliances.store',
                'show'    => 'admin.appliances.show',
                'edit'    => 'admin.appliances.edit',
                'update'  => 'admin.appliances.update',
                'destroy' => 'admin.appliances.destroy',
            ]);

        Route::resource('questions', AdminQuestionController::class)
            ->names([
                'index'   => 'admin.questions.index',
                'create'  => 'admin.questions.create',
                'store'   => 'admin.questions.store',
                'show'    => 'admin.questions.show',
                'edit'    => 'admin.questions.edit',
                'update'  => 'admin.questions.update',
                'destroy' => 'admin.questions.destroy',
            ]);

        Route::resource('firmwares', AdminFirmwareController::class)
            ->names([
                'index' => 'admin.firmwares.index',
                'show' => 'admin.firmwares.show',
                'create' => 'admin.firmwares.create',
                'edit' => 'admin.firmwares.edit',
                'update' => 'admin.firmwares.update',
                'destroy' => 'admin.firmwares.destroy',
            ]);

        Route::get('/firmwares/download/{filename}', [AdminFirmwareController::class, 'download'])->name('admin.firmwares.download');

        Route::get('/custom/edit', [CustomizationController::class, 'edit'])->name('custom.edit');
        Route::post('/custom/update', [CustomizationController::class, 'update'])->name('custom.update');

        Route::get('/comments', [AdminCommentController::class, 'index'])->name('admin.comments.index');
        Route::get('/comments/{comment}/edit', [AdminCommentController::class, 'edit'])->name('admin.comments.edit');
        Route::put('/comments/{comment}', [AdminReplyController::class, 'update'])->name('admin.comments.update');

        Route::get('/replies', [AdminReplyController::class, 'index'])->name('admin.replies.index');
        Route::get('/replies/{reply}/edit', [AdminReplyController::class, 'edit'])->name('admin.replies.edit');
        Route::put('/replies/{reply}', [AdminReplyController::class, 'update'])->name('admin.replies.update');

        Route::get('/logs', [RequestLogController::class, 'index'])->name('admin.logs.index');
    }
);

Route::group(
    ['prefix' => 'heturion'],
    function () {
        Route::get('login', [AuthSessionController::class, 'showLoginForm'])->name('admin.login');
        Route::post('login', [AuthSessionController::class, 'login']);
        Route::post('logout', [AuthSessionController::class, 'logout'])
            ->name('admin.logout');
    }
);

require __DIR__ . '/auth.php';
