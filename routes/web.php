<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CouponController;

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

    Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:5,1');

    Route::post('/api/coupons/validate', [CouponController::class, 'validateCoupon']);
});

/*
|--------------------------------------------------------------------------
| 前台公開 API（無需登入）
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| 前台 Blade 頁面（公開，不需登入）
|--------------------------------------------------------------------------
*/
Route::get('/',          [PageController::class, 'home'])->name('front.home');

/*
|--------------------------------------------------------------------------
| 前台 Blade 頁面（需要登入，未登入自動轉 /login）
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {});

/*
|--------------------------------------------------------------------------
| 後台（需要 admin 角色）
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {

    Route::get('/me',        [AdminAuthController::class, 'me']);
    Route::post('/logout',   [AdminAuthController::class, 'logout']);

    Route::get('/coupons',      [PageController::class, 'couponIndex'])->name('admin.coupons.index');
    Route::get('/coupons/{id}', [PageController::class, 'couponDetail'])->where('id', '[0-9]+')->name('admin.coupons.show');
});

Route::middleware(['auth', 'admin'])->prefix('api/admin')->group(function () {
    Route::get('/coupons',                       [CouponController::class, 'adminIndex']);
    Route::post('/coupons',                      [CouponController::class, 'store']);
    Route::get('/coupons/{id}',                  [CouponController::class, 'adminShow'])->where('id', '[0-9]+');
    Route::put('/coupons/{id}',                  [CouponController::class, 'update'])->where('id', '[0-9]+');
    Route::patch('/coupons/{id}/toggle-active',  [CouponController::class, 'toggleActive'])->where('id', '[0-9]+');
    Route::patch('/coupons/batch-status',        [CouponController::class, 'batchUpdateStatus']);
});
