<?php

use App\Http\Controllers\Admin\ApplianceController;
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


use App\Http\Controllers\Admin\AuthSessionController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Admin\CustomizationController;
use App\Http\Controllers\Admin\FirmwareController;
use App\Http\Controllers\Public\ApplianceController as PublicApplianceController;
use App\Http\Controllers\Public\CommentController;
use App\Http\Controllers\Public\FirmwareController as PublicFirmwareController;
use App\Http\Controllers\Public\LikeController;
use App\Http\Controllers\Public\ProfileController;

use App\Http\Controllers\Public\QuestionController;
use App\Http\Controllers\Public\ReplyController;

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



Route::resource('questions', QuestionController::class);



Route::group(
    ['middleware' => ['auth:web', 'verified']],
    function () {
        Route::get('/profile', [ProfileController::class, 'showProfile'])->name('profile');
        Route::post('/update-profile', [ProfileController::class, 'updateProfile'])->name('update-profile');

        Route::post('/{type}/{id}/comments', [CommentController::class, 'store'])->name('comments.store');
        Route::group(['prefix' => '/comments'], function () {
            Route::put('/{id}', [CommentController::class, 'update'])->name('comments.update');
            Route::delete('/{id}', [CommentController::class, 'destroy'])->name('comments.destroy');
            Route::post('/{id}/replies', [ReplyController::class, 'store'])->name('comments.replies.store');
        });
        Route::put('/replies/{id}', [ReplyController::class, 'update'])->name('comments.replies.update');
        Route::delete('/replies/{id}', [ReplyController::class, 'destroy'])->name('comments.replies.destroy');

        Route::prefix('/{type}/{id}/')->group(function () {
            Route::post('/like', [LikeController::class, 'like'])->name('like');
            Route::post('/dislike', [LikeController::class, 'dislike'])->name('dislike');
        });

        Route::prefix('/questions')->group(function () {
            Route::get('/create', [QuestionController::class, 'create'])->name('questions.create');
            Route::post('/', [QuestionController::class, 'store'])->name('questions.store');
            Route::delete('/{id}', [QuestionController::class, 'destroy'])->name('questions.destroy');
            // Переопределение методов ресурсного контроллера
        });


        Route::get('/firmwares/download/{filename}', [PublicFirmwareController::class, 'download'])->name('firmwares.download');
    }
);

Route::get('/categories/{category}', [PublicCategoryController::class, 'show'])->name('public.categories.show');
Route::get('/appliances/{appliance}', [PublicApplianceController::class, 'show'])->name('public.appliances.show');

Route::get('/tag/{slug}', [PublicTagController::class, 'showTagArticles'])->name('tag.articles');


Route::get('/firmwares', [PublicFirmwareController::class, 'index'])->name('firmwares.index');
Route::get('/firmwares/{firmware}', [PublicFirmwareController::class, 'show'])->name('firmwares.show');





Route::get('/', function () {
    return redirect('/articles');
});

Route::group(
    ['prefix' => 'articles'],
    function () {
        Route::get('/', [PublicPostController::class, 'index'])->name('articles.index');
        Route::get('/{article}', [PublicPostController::class, 'show'])->name('articles.show');
    }
);


Route::group(
    ['middleware' => 'auth:admin', 'prefix' => 'heturion'],
    function () {
        Route::get('/', [MainController::class, 'index'])->name('admin.index');
        Route::resource('tasks', TaskController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('tags', TagController::class);
        Route::resource('posts', PostController::class);
        Route::resource('users', AdminUserController::class);
        Route::resource('appliances', ApplianceController::class);
        Route::get('/deleted-users', [DeletedUserController::class, 'index'])->name('deleted-users.index');
        Route::get('/deleted-users/restore/{id}', [DeletedUserController::class, 'restore'])->name('deleted-users.restore');


        Route::get('/custom/edit', [CustomizationController::class, 'edit'])->name('custom.edit');
        Route::post('/custom/update', [CustomizationController::class, 'update'])->name('custom.update');
        Route::get('/download/{filename}', [FirmwareController::class, 'downloadFile'])->name('admin.download');
        Route::get('/firmwares', [FirmwareController::class, 'index'])->name('admin.firmwares.index');
        Route::get('/firmwares/duplicates', [FirmwareController::class, 'getDuplicates'])->name('admin.firmwares.duplicates'); // Порядок объявления до /firmwares/{firmware}
        Route::get('/firmwares/search', [FirmwareController::class, 'search'])->name('admin.firmwares.search'); // Порядок объявления до /firmwares/{firmware}
        Route::get('/firmwares/{firmware}/edit', [FirmwareController::class, 'edit'])->name('admin.firmwares.edit');
        Route::put('/firmwares/{firmware}', [FirmwareController::class, 'update'])->name('admin.firmwares.update');
        Route::get('/firmwares/{firmware}', [FirmwareController::class, 'show'])->name('admin.firmwares.show');
        Route::delete('/firmwares/{firmware}', [FirmwareController::class, 'destroy'])->name('admin.firmwares.destroy');

        Route::get('/comments', [AdminCommentController::class, 'index'])->name('admin.comments.index');
        Route::get('/comments/{id}/edit', [AdminCommentController::class, 'edit'])->name('admin.comments.edit');

        Route::delete('/firmwares/{firmware}', [FirmwareController::class, 'show'])->name('admin.firmwares.destroy');

        Route::put('/markDuplicates', [FirmwareController::class, 'markDuplicates'])->name('admin.firmwares.markDuplicates');

        Route::delete('/firmwares/delete', [FirmwareController::class, 'removeSecondFromAllDuplicates'])->name('admin.firmwares.remove_duplicates');
        Route::resource('questions', App\Http\Controllers\Admin\QuestionController::class);
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
