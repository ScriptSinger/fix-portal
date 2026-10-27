<?php

use App\Http\Controllers\Api\FirmwareController;
use App\Http\Controllers\Api\Public\MasterController;
use App\Http\Controllers\Api\Public\UserImageController;


use App\Http\Controllers\Public\ReactionController;
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

Route::get('/firmwares', [FirmwareController::class, 'index'])->name('api.firmwares.index');
Route::get('/masters', [MasterController::class, 'index'])->name('api.masters.index');

Route::group(
    [
        // Только пользователи сайта: контент привязывается по user_id, админам тут делать нечего.
        'middleware' => 'auth:web',
    ],
    function () {
        Route::post('/reactions/{type}/{id}', [ReactionController::class, 'toggle'])->whereIn('type', ['comment', 'reply'])->name('reactions.toggle');

        // Загружать могут только пользователи с подтверждённой почтой и не чаще 20 файлов в минуту
        Route::post('users-images', [UserImageController::class, 'upload'])
            ->middleware(['verified', 'throttle:20,1'])
            ->name('api.users.images.upload');
        Route::delete('users-images/{image}', [UserImageController::class, 'destroy'])->name('api.users.images.destroy');

    }
);
