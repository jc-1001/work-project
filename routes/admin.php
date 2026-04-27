<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {

    // 商品管理
    Route::get('/products',       [ProductController::class, 'index']);
    Route::get('/products/{id}',  [ProductController::class, 'show']);
    Route::post('/products',      [ProductController::class, 'store']);
    Route::post('/products/{id}', [ProductController::class, 'update']);

    // 訂單管理
    Route::get('/orders',        [OrderController::class, 'adminIndex']);
    Route::get('/orders/{id}',   [OrderController::class, 'adminShow']);
});
