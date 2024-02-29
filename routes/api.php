<?php

use App\Http\Controllers\HomeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserSettingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'api','prefix' => 'v1'], function () {
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/refresh', [AuthController::class, 'refreshToken']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::middleware('jwtauth')->group(function () {
            Route::patch('/fcm-token', [NotificationController::class, 'updateToken'])->name('fcmToken');
            Route::post('/send-notification',[NotificationController::class,'notification'])->name('notification');
            Route::get('/add-product-to-cart/{id}', [HomeController::class, 'addProduct']);
            Route::get('add-favorite-product/{id}', [HomeController::class, 'addFavoriteProduct']);
        });
    });
    Route::middleware('jwtauth')->group(function () {
        Route::prefix('members/me')->group(function () {
            Route::post('/user-infor/change-password', [UserSettingController::class, 'changePassword']);
            Route::post('/user-infor/change-email', [UserSettingController::class, 'changeEmail']);
            Route::get('/user-profile', [UserSettingController::class, 'getProfile']);
            Route::post('/update-profile', [UserSettingController::class, 'updateProfile']);
            Route::post('/update-setting-language', [UserSettingController::class, 'updateLanguage']);
        });

        Route::get('/get-list-favorite', [HomeController::class, 'getListFavorite']);
        Route::post('remove-favorite-product', [HomeController::class, 'removeFavoriteProduct']);
    });
    Route::get('verify-email/{id}', [UserSettingController::class, 'verifyChangeEmail']);
    Route::post('upload-image', [UserSettingController::class, 'upload']);
    Route::get('/get-slide', [NotificationController::class, 'getSlide']);
    Route::get('/get-categories', [NotificationController::class, 'getCategories']);
    Route::get('/get-data-home', [HomeController::class, 'getDataHome']);
    Route::get('/get-articles', [HomeController::class, 'getListArticles']);
    Route::get('/get-detail-article/{id}', [HomeController::class, 'getDetailArticle']);
    Route::get('/get-detail-product/{id}', [HomeController::class, 'getDetailProduct']);
});


