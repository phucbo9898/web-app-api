<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserSettingController;
use App\Http\Controllers\NotificationController;

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

Route::prefix('v1')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('/login', [AuthController::class, 'login']);
            Route::get('/refresh', [AuthController::class, 'refreshToken']);

            /**
             * @QA\Post(
             *   path="api/v1/auth/register",
             *   summary="Create A User",
             *   operationId="Create A User",
             *   tags={"Auth"},
             *   security={
             *       {"ApiKeyAuth": {}}
             *   },
             *   @QA\Parameter(
             *       name="name",
             *       in="formData",
             *       required=true,
             *       type="string"
             *   ),
             *   @QA\Parameter(
             *       name="email",
             *       in="formData",
             *       required=true,
             *       type="string"
             *   ),
             *   @QA\Parameter(
             *       name="role",
             *       in="formData",
             *       required=true,
             *       type="string"
             *   ),
             *   @QA\Parameter(
             *       name="status",
             *       in="formData",
             *       required=true,
             *       type="string"
             *   ),
             *   @QA\Parameter(
             *       name="password",
             *       in="formData",
             *       required=true,
             *       type="string"
             *   ),
             *   @QA\Response(
             *     response=200,
             *     description="successful operation"
             *   ),
             *   @QA\Response(
             *     response=406,
             *     description="not acceptable"
             *   ),
             *   @QA\Response(
             *     response=500,
             *     description="internal server error
             *   ")
             * )
             *
             */
            Route::post('/register', [AuthController::class, 'register']);
            Route::post('/logout', [AuthController::class, 'logout'])->middleware('jwtauth');
        });
    });
    // start example push notification from back-end to front-end
    Route::middleware('jwtauth')->group(function () {
        Route::post('/send-notification',[NotificationController::class,'notification']);
    });
    // end example push notification from back-end to front-end

    Route::controller(UserSettingController::class)->group(function () {
        Route::prefix('members/me')->group(function () {
            Route::middleware('jwtauth')->group(function () {
                Route::get('/user-profile', 'getProfile');
                Route::post('/update-profile', 'updateProfile');
                Route::post('/change-password', 'changePassword');
                Route::post('/change-email', 'changeEmail');
                Route::post('/update-setting-language', 'updateLanguage');
                Route::patch('/update-device-token', 'updateToken');
            });
        });
        Route::get('verify-email/{id}', 'verifyChangeEmail');
        Route::get('verify-email/{id}', 'verifyChangeEmail');
    });

    Route::controller(HomeController::class)->group(function () {
        Route::get('/get-slide', 'getSlide');
        Route::get('/get-categories', 'getCategories');
        Route::get('/get-data-home', 'getDataHome');
        Route::get('/get-articles', 'getListArticles');
        Route::get('/get-detail-article/{id}', 'getDetailArticle');
        Route::get('/get-detail-product/{id}', 'getDetailProduct');
        Route::middleware('jwtauth')->group(function () {
            Route::get('/get-list-favorite', 'getListFavorite');
            Route::get('add-favorite-product/{id}', 'addFavoriteProduct');
            Route::post('remove-favorite-product', 'removeFavoriteProduct');
            Route::get('/add-product-to-cart/{id}', 'addProduct');
        });
    });

    Route::prefix('cart')->group(function () {
        Route::middleware('jwtauth')->group(function () {
            Route::get('/list-product-in-cart', [CartController::class, 'getList']);
            Route::post('/add-product-to-cart', [CartController::class, 'addProductToCart']);
            Route::post('/get-voucher', [CartController::class, 'getVoucher']);
            Route::post('/check-out', [CartController::class, 'checkout']);
        });
    });
});


