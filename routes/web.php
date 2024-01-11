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
use App\Http\Controllers\SearchController;

use App\Http\Controllers\Admin\AuthSessionController;
use App\Http\Controllers\Admin\CustomizationController;
use App\Http\Controllers\Admin\FirmwareController;
use App\Http\Controllers\Public\ApplianceController as PublicApplianceController;
use App\Http\Controllers\Public\CommentController;
use App\Http\Controllers\Public\FirmwareController as PublicFirmwareController;

use App\Http\Controllers\Public\ProfileController;

use App\Http\Controllers\Public\QuestionController;
use App\Http\Controllers\Public\ReplyController;

use function Laravel\Prompts\search;

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


Route::get('questions/search', [QuestionController::class, 'search'])->name('questions.search');
Route::resource('questions', QuestionController::class);



Route::group(
    ['middleware' => ['auth:web', 'verified']],
    function () {
        Route::get('/profile', [ProfileController::class, 'showProfile'])->name('profile');
        Route::post('/update-profile', [ProfileController::class, 'updateProfile'])->name('update-profile');

        Route::group(['prefix' => 'questions'], function () {
            Route::group(['prefix' => '{question_id}/comments'], function () {
                Route::post('/', [CommentController::class, 'storeQuestionComment'])->name('question.comment.store');


                Route::group(['prefix' => '{comment_id}/replies'], function () {
                    Route::post('/', [CommentController::class, 'storeQuestionComment'])->name('question.comment.reply.store');
                });
            });
        });


        Route::prefix('comments')->group(function () {

            Route::post('/commentable/{commentableType}/{commentableId}', [CommentController::class, 'store'])->name('comments.store');
            Route::put('/{comment_id}', [CommentController::class, 'update'])->name('comments.update');
            Route::delete('/{comment_id}', [CommentController::class, 'destroy'])->name('comments.destroy');

            Route::prefix('/{comment_id}')->group(function () {
                Route::post('/like', [CommentController::class, 'like'])->name('comments.like');
                Route::post('/dislike', [CommentController::class, 'dislike'])->name('comments.dislike');
            });


            Route::post('/{comment_id}/replies', [ReplyController::class, 'store'])->name('comments.replies.store');
            Route::put('/replies/{reply_id}', [ReplyController::class, 'update'])->name('comments.replies.update');
            Route::delete('/replies/{reply_id}', [ReplyController::class, 'destroy'])->name('comments.replies.destroy');
        });



        Route::get('questions/create', [QuestionController::class, 'create'])->name('questions.create');
        Route::post('questions', [QuestionController::class, 'store'])->name('questions.store');
        Route::get('/firmwares/download/{filename}', [PublicFirmwareController::class, 'download'])->name('firmwares.download');
    }
);

Route::get('/categories/{category}', [PublicCategoryController::class, 'show'])->name('public.categories.show');
Route::get('/appliances/{appliance}', [PublicApplianceController::class, 'show'])->name('public.applinaces.show');

Route::get('/tag/{slug}', [PublicTagController::class, 'showTagArticles'])->name('tag.articles');
Route::get('/search', [SearchController::class, 'index'])->name('search'); // поправить

Route::get('/firmwares', [PublicFirmwareController::class, 'index'])->name('firmwares.index');
Route::get('/firmwares/search', [PublicFirmwareController::class, 'search'])->name('firmwares.search'); // Порядок объявления до /firmwares/{firmware}
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
        Route::get('/firmwares/{firmware}', [FirmwareController::class, 'show'])->name('admin.firmwares.show');
        Route::delete('/firmwares/{firmware}', [FirmwareController::class, 'show'])->name('admin.firmwares.destroy');

        Route::put('/markDuplicates', [FirmwareController::class, 'markDuplicates'])->name('admin.firmwares.markDuplicates');

        Route::delete('/firmwares/delete', [FirmwareController::class, 'removeSecondFromAllDuplicates'])->name('admin.firmwares.remove_duplicates');
    }
);



Route::group(
    ['prefix' => 'heturion'],
    function () {
        Route::get('login', [AuthSessionController::class, 'showLoginForm'])->name('admin.login');
        Route::post('login', [AuthSessionController::class, 'login']);
    }
);


require __DIR__ . '/auth.php';
