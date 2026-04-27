<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Models\Product;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

// 公開路由
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/login',    [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::get('/categories', [ProductController::class, 'categories']);
Route::get('/products', fn () => Product::with('category')->where('is_active', 1)->get());

// 需要登入的路由
Route::middleware('auth')->group(function () {
    Route::post('/logout',       [AuthController::class, 'logout']);
    Route::get('/me',            [AuthController::class, 'me']);
    Route::put('/user/update',   [UserController::class, 'update']);
    Route::get('/orders',        [OrderController::class, 'index']);
    Route::post('/orders',       [OrderController::class, 'store']);
});
