<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 認證頁面（僅未登入者可進入，已登入者自動轉首頁）
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login',           [AuthController::class, 'showLogin'])->name('login');
    Route::get('/register',        [AuthController::class, 'showRegister'])->name('register');
    Route::get('/admin/login',     [PageController::class, 'adminLogin'])->name('admin.login');

    
    Route::get('/forgot-password', [PageController::class, 'forgotPassword']);
    Route::get('/reset-password',  [PageController::class, 'resetPassword']);
    
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1');
    Route::post('/reset-password',  [AuthController::class, 'resetPassword'])->middleware('throttle:3,1');
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
Route::get('/', [PageController::class, 'home'])->name('front.home');

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
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {});
