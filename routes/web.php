<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

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


Route::post('/login',       [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/register',    [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1');

/*
|--------------------------------------------------------------------------
| 一般使用者 API（需登入，角色：user / admin 皆可）
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout',     [AuthController::class, 'logout']);
    Route::get('/me',          [AuthController::class, 'me']);
    Route::put('/user/update', [UserController::class, 'update']);
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
Route::middleware('auth')->group(function () {});

/*
|--------------------------------------------------------------------------
| 後台（需要 admin 角色）
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/me',        [AdminAuthController::class, 'me']);
    Route::post('/logout',   [AdminAuthController::class, 'logout']);

    Route::get('/products',         [PageController::class, 'adminProductsIndex']);
    Route::get('/products/create',   [PageController::class, 'adminProductsCreate']);
    Route::get('/products/{id}',     [PageController::class, 'adminProductsShow'])->where('id', '[0-9]+');

    Route::get('/user',       [PageController::class, 'adminUsersIndex'])->name('admin.users.index');
    Route::get('/user/{id}',  [PageController::class, 'adminUsersShow'])->where('id', '[0-9]+')->name('admin.users.show');

    Route::get('/orders',      [PageController::class, 'adminOrdersIndex']);
    Route::get('/orders/{id}', [PageController::class, 'adminOrdersShow'])->where('id', '[0-9]+');
});

Route::middleware(['auth', 'admin'])->prefix('api/admin')->group(function () {
    Route::patch('/products/batch-status',              [ProductController::class, 'batchUpdateStatus']);
    Route::get('/products',                             [ProductController::class, 'index']);
    Route::get('/products/{id}',                        [ProductController::class, 'show'])->where('id', '[0-9]+');
    Route::post('/products',                            [ProductController::class, 'store']);
    Route::post('/products/{id}',                       [ProductController::class, 'update'])->where('id', '[0-9]+');
    Route::delete('/products/{id}/images/{imageId}',    [ProductController::class, 'deleteImage'])->where(['id' => '[0-9]+', 'imageId' => '[0-9]+']);
});
/*
|--------------------------------------------------------------------------
| 後台管理 API（auth + admin，由 Vue 元件透過 axios 呼叫）
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('api/admin')->group(function () {

    Route::get('/users',                        [UserController::class, 'adminIndex']);
    Route::get('/users/{id}',                   [UserController::class, 'adminShow'])->where('id', '[0-9]+');
    Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive']);

    Route::get('/orders',                      [OrderController::class, 'adminIndex']);
    Route::get('/orders/{id}',                 [OrderController::class, 'adminShow'])->where('id', '[0-9]+');
    Route::patch('/orders/batch-status',       [OrderController::class, 'batchUpdateStatus']);
    Route::patch('/orders/{order}/status',     [OrderController::class, 'updateStatus']);
    Route::patch('/orders/{order}/cancel',     [OrderController::class, 'cancel']);
    Route::patch('/orders/{order}/return',     [OrderController::class, 'return']);
});
