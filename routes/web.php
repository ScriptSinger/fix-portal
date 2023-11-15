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

use App\Http\Controllers\Admin\AuthSessionController;
use App\Http\Controllers\Admin\CustomizationController;
use App\Http\Controllers\Comment\PostCommentController;


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






Route::get('/category/{slug}', [PublicCategoryController::class, 'showCategoryArticles'])->name('category.articles');
Route::get('/tag/{slug}', [PublicTagController::class, 'showTagArticles'])->name('tag.articles');
Route::get('/search', [SearchController::class, 'index'])->name('search');

Route::get('/', function () {
    return redirect('/articles');
});

Route::group(['prefix' => 'articles'], function () {
    Route::get('/', [PublicPostController::class, 'index'])->name('article.index');
    Route::get('/{slug}', [PublicPostController::class, 'show'])->name('article.show');
    Route::group(['prefix' => '{article_id}/comments'], function () {
        Route::post('/', [PostCommentController::class, 'store'])->name('article.comment.store');
    });
});


Route::group(
    ['middleware' => 'auth:admin', 'prefix' => 'admin'],
    function () {
        Route::get('/', [MainController::class, 'index'])->name('admin.index');
        Route::resource('tasks', TaskController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('tags', TagController::class);
        Route::resource('posts', PostController::class);
        Route::resource('users', AdminUserController::class);
        Route::get('/deleted-users', [DeletedUserController::class, 'index'])->name('deleted-users.index');
        Route::get('/deleted-users/restore/{id}', [DeletedUserController::class, 'restore'])->name('deleted-users.restore');


        Route::get('/custom/edit', [CustomizationController::class, 'edit'])->name('custom.edit');
        Route::post('/custom/update', [CustomizationController::class, 'update'])->name('custom.update');
    }
);





Route::group(
    ['prefix' => 'admin'],
    function () {
        Route::get('login', [AuthSessionController::class, 'showLoginForm'])->name('admin.login');
        Route::post('login', [AuthSessionController::class, 'login']);
    }
);







require __DIR__ . '/auth.php';
