<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
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
// POST /admin/login 由 TRAIN-156 的 AdminAuthController 實作

/*
|--------------------------------------------------------------------------
| 一般使用者 API（需登入，角色：user / admin 皆可）
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout',     [AuthController::class, 'logout']);
    Route::get('/me',          [AuthController::class, 'me']);
    Route::put('/user/update', [UserController::class, 'update']);

    // 訂單
    // Route::get('/orders',  [OrderController::class, 'index']);
    // Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:5,1');
});

/*
|--------------------------------------------------------------------------
| 前台公開 API（無需登入）
|--------------------------------------------------------------------------
*/
Route::get('/categories',    [ProductController::class, 'categories']);
Route::get('/products',      [ProductController::class, 'frontIndex']);
Route::get('/products/{id}', [ProductController::class, 'frontShow'])->where('id', '[0-9]+');

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
    // 之後在此新增需要登入才能看的前台頁面
    // Route::get('/orders',  [PageController::class, 'orderIndex'])->name('front.orders');
    // Route::get('/profile', [PageController::class, 'profile'])->name('front.profile');
});

/*
|--------------------------------------------------------------------------
| 後台（需要 admin 角色）
| - Blade 頁面：瀏覽器直接進入時由 PageController 回傳 HTML
| - API 資料：Vue 組件透過 axios 呼叫同一 URL，由各 Controller 回傳 JSON
| - 未登入 / 非 admin → EnsureIsAdmin 統一轉向 /admin/login（Blade）
|             或回傳 403 JSON（axios）
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {

    // 商品管理
    // Route::get('/products',       [ProductController::class, 'index']);
    // Route::get('/products/{id}',  [ProductController::class, 'show'])->where('id', '[0-9]+');
    // Route::post('/products',      [ProductController::class, 'store']);
    // Route::post('/products/{id}', [ProductController::class, 'update'])->where('id', '[0-9]+');

    // 訂單管理
    // Route::get('/orders',      [OrderController::class, 'adminIndex']);
    // Route::get('/orders/{id}', [OrderController::class, 'adminShow'])->where('id', '[0-9]+');
});
