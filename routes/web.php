<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ComplaintController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 認證頁面（僅未登入者可進入，已登入者自動轉首頁）
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login',        [AuthController::class, 'showLogin'])->name('login');
    Route::get('/register',     [AuthController::class, 'showRegister'])->name('register');
    Route::get('/admin/login',  [PageController::class, 'adminLogin'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1');
});

Route::post('/login',    [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');

/*
|--------------------------------------------------------------------------
| 一般使用者 API（需登入，角色：user / admin 皆可）
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout',     [AuthController::class, 'logout']);
    Route::get('/me',          [AuthController::class, 'me']);
    Route::put('/user/update', [UserController::class, 'update']);

    Route::get('/orders',  [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:5,1');

    Route::post('/api/coupons/validate', [CouponController::class, 'validateCoupon']);
});

/*
|--------------------------------------------------------------------------
| 前台公開 API（無需登入）
|--------------------------------------------------------------------------
*/
Route::get('/categories',    [ProductController::class, 'categories']);
Route::get('/products',      [ProductController::class, 'frontIndex']);
Route::get('/products/{id}', [ProductController::class, 'frontShow'])->where('id', '[0-9]+');

Route::get('/advertisement/active', [AdvertisementController::class, 'active']);
Route::post('/newMessage',          [ContactMessageController::class, 'store']);
/*
|--------------------------------------------------------------------------
| 前台 Blade 頁面（公開，不需登入）
|--------------------------------------------------------------------------
*/
Route::get('/',          [PageController::class, 'home'])->name('front.home');
Route::get('/shop',      [PageController::class, 'shopIndex'])->name('front.shop');
Route::get('/shop/{id}', [PageController::class, 'shopShow'])->where('id', '[0-9]+')->name('front.shop.show');
Route::get('/403',       [PageController::class, 'forbidden']);

/*
|--------------------------------------------------------------------------
| 前台 Blade 頁面（需要登入，未登入自動轉 /login）
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [PageController::class, 'profile'])->name('front.profile');
    Route::get('/profile/orders', [PageController::class, 'orderIndex'])->name('front.profile.order');
});

/*
|--------------------------------------------------------------------------
| 後台（需要 admin 角色）
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/advertisements',      [PageController::class, 'adminAdvertisements'])->name('admin.advertisements');
    Route::get('/advertisements/{id}', [PageController::class, 'adminAdvertisementShow'])->where('id', '[0-9]+')->name('admin.advertisements.show');

    Route::prefix('api')->group(function () {
        Route::get('/advertisements',         [AdvertisementController::class, 'index']);
        Route::post('/advertisements',        [AdvertisementController::class, 'store']);
        Route::get('/advertisements/{id}',    [AdvertisementController::class, 'show'])->where('id', '[0-9]+');
        Route::put('/advertisements/{id}',    [AdvertisementController::class, 'update'])->where('id', '[0-9]+');
        Route::delete('/advertisements/{id}', [AdvertisementController::class, 'destroy'])->where('id', '[0-9]+');
    });

    Route::get('/me',        [AdminAuthController::class, 'me']);
    Route::post('/logout',   [AdminAuthController::class, 'logout']);

    Route::get('/dashboard',       [PageController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'index']);

    Route::get('/products',         [PageController::class, 'adminProductsIndex']);
    Route::get('/products/create',   [PageController::class, 'adminProductsCreate']);
    Route::get('/products/{id}',     [PageController::class, 'adminProductsShow'])->where('id', '[0-9]+');

    Route::get('/user',       [PageController::class, 'adminUsersIndex'])->name('admin.users.index');
    Route::get('/user/{id}',  [PageController::class, 'adminUsersShow'])->where('id', '[0-9]+')->name('admin.users.show');

    Route::get('/orders',      [PageController::class, 'adminOrdersIndex']);
    Route::get('/orders/{id}', [PageController::class, 'adminOrdersShow'])->where('id', '[0-9]+');

    Route::get('/forbidden', [PageController::class, 'adminForbidden'])->name('admin.forbidden');

    Route::get('/coupons',      [PageController::class, 'couponIndex'])->name('admin.coupons.index');
    Route::get('/coupons/{id}', [PageController::class, 'couponDetail'])->where('id', '[0-9]+')->name('admin.coupons.show');

    Route::middleware('super_admin')->group(function () {
        Route::get('/administrators',      [PageController::class, 'administratorsIndex'])->name('administrators.index');
        Route::get('/administrators/{id}', [PageController::class, 'administratorsShow'])->where('id', '[0-9]+')->name('administrators.show');
    });
});

Route::middleware(['auth', 'admin'])->prefix('api/admin')->group(function () {
    Route::patch('/products/batch-status',              [ProductController::class, 'batchUpdateStatus']);
    Route::get('/products',                             [ProductController::class, 'index']);
    Route::get('/products/{id}',                        [ProductController::class, 'show'])->where('id', '[0-9]+');
    Route::post('/products',                            [ProductController::class, 'store']);
    Route::post('/products/{id}',                       [ProductController::class, 'update'])->where('id', '[0-9]+');
    Route::delete('/products/{id}/images/{imageId}',    [ProductController::class, 'deleteImage'])->where(['id' => '[0-9]+', 'imageId' => '[0-9]+']);

    Route::get('/users',                        [UserController::class, 'adminIndex']);
    Route::get('/users/{id}',                   [UserController::class, 'adminShow'])->where('id', '[0-9]+');
    Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive']);

    Route::get('/orders',                      [OrderController::class, 'adminIndex']);
    Route::get('/orders/{id}',                 [OrderController::class, 'adminShow'])->where('id', '[0-9]+');
    Route::patch('/orders/batch-status',       [OrderController::class, 'batchUpdateStatus']);
    Route::patch('/orders/{order}/status',     [OrderController::class, 'updateStatus']);
    Route::patch('/orders/{order}/cancel',     [OrderController::class, 'cancel']);
    Route::patch('/orders/{order}/return',     [OrderController::class, 'return']);

    Route::get('/coupons',                       [CouponController::class, 'adminIndex']);
    Route::post('/coupons',                      [CouponController::class, 'store']);
    Route::get('/coupons/{id}',                  [CouponController::class, 'adminShow'])->where('id', '[0-9]+');
    Route::put('/coupons/{id}',                  [CouponController::class, 'update'])->where('id', '[0-9]+');
    Route::patch('/coupons/{id}/toggle-active',  [CouponController::class, 'toggleActive'])->where('id', '[0-9]+');
    Route::patch('/coupons/batch-status',        [CouponController::class, 'batchUpdateStatus']);
});

Route::middleware(['auth', 'admin', 'super_admin'])->prefix('api/admin')->group(function () {
    Route::get('/administrators',                        [UserController::class, 'administratorIndex']);
    Route::get('/administrators/{id}',                   [UserController::class, 'administratorShow'])->where('id', '[0-9]+');
    Route::patch('/administrators/{id}/toggle-active',   [UserController::class, 'administratorToggleActive'])->where('id', '[0-9]+');
    Route::patch('/administrators/{id}',                 [UserController::class, 'adminUpdate'])->where('id', '[0-9]+');
    Route::post('/administrators',                       [UserController::class, 'adminStore']);
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/reply',      [PageController::class, 'adminReplyList']);
    Route::get('/reply/{id}', [PageController::class, 'adminReply'])->where('id', '[0-9]+');
});

Route::middleware(['auth', 'admin'])->prefix('api/admin')->group(function () {
    Route::get('/contactMessages',                               [ContactMessageController::class, 'adminIndex']);
    Route::get('/contactMessages/{id}',                          [ContactMessageController::class, 'adminShow'])->where('id', '[0-9]+');
    Route::patch('/contactMessages/batch-status',                [ContactMessageController::class, 'batchUpdateStatus']);
    Route::patch('/contactMessages/{contactMessage}/status',     [ContactMessageController::class, 'updateStatus']);
    Route::patch('/contactMessages/{contactMessage}/reply',      [ContactMessageController::class, 'adminReplyStore']);
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/complaints',      [PageController::class, 'adminComplaintIndex']);
    Route::get('/complaints/{id}', [PageController::class, 'adminComplaintShow'])->where('id', '[0-9]+');
});

Route::middleware(['auth', 'admin'])->prefix('api/admin')->group(function () {
    Route::get('/complaints',               [ComplaintController::class, 'adminIndex']);
    Route::get('/complaints/{id}',          [ComplaintController::class, 'adminShow'])->where('id', '[0-9]+');
    Route::patch('/complaints/{id}',        [ComplaintController::class, 'updateStatus'])->where('id', '[0-9]+');
    Route::post('/complaints/batch',        [ComplaintController::class, 'batchUpdateStatus']);
});
