<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 認證頁面（僅未登入者可進入，已登入者自動轉首頁）
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login',       [AuthController::class, 'showLogin'])->name('login');
    Route::get('/register',    [AuthController::class, 'showRegister'])->name('register');
    Route::get('/admin/login', [PageController::class, 'adminLogin'])->name('admin.login');
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

    Route::get('/orders/latest', [OrderController::class, 'latest']);
    Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:5,1');
});

/*
|--------------------------------------------------------------------------
| 前台公開 API（無需登入）
|--------------------------------------------------------------------------
*/
Route::get('/categories',    [ProductController::class, 'categories']);
Route::get('/products',      [ProductController::class, 'frontIndex']);
Route::get('/products/{id}', [ProductController::class, 'frontShow'])->where('id', '[0-9]+');
Route::get('/products/{id}/reviews', [ReviewController::class, 'index'])->where('id', '[0-9]+');

Route::middleware('auth')->group(function () {
    Route::post('/products/{id}/reviews', [ReviewController::class, 'store'])->where('id', '[0-9]+');
    Route::patch('/reviews/{id}', [ReviewController::class, 'update'])->where('id', '[0-9]+');
});

/*
|--------------------------------------------------------------------------
| 前台 Blade 頁面（公開，不需登入）
|--------------------------------------------------------------------------
*/
Route::get('/',          [PageController::class, 'home'])->name('front.home');
Route::get('/shop',      [PageController::class, 'shopIndex'])->name('front.shop');
Route::get('/shop/{id}', [PageController::class, 'shopShow'])->where('id', '[0-9]+')->name('front.shop.show');


/*
|--------------------------------------------------------------------------
| 前台 Blade 頁面（需要登入，未登入自動轉 /login）
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/cart',  [PageController::class, 'cart'])->name('front.cart');
    Route::get('/order', [PageController::class, 'order'])->name('front.order');
});

/*
|--------------------------------------------------------------------------
| 後台（需要 admin 角色）
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {});
