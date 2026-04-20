<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

// 後台需要登入的路由
Route::middleware('auth')->group(function () {
    // 商品管理
    Route::get('/admin/products',         [ProductController::class, 'index']);
    Route::post('/admin/products',        [ProductController::class, 'store']);
    Route::get('/admin/products/{id}',    [ProductController::class, 'show'])->where('id', '[0-9]+');
    Route::put('/admin/products/{id}',    [ProductController::class, 'update'])->where('id', '[0-9]+');
    Route::get('/admin/categories',       [ProductController::class, 'categories']);

    // 訂單管理
    Route::get('/admin/orders',           [OrderController::class, 'adminIndex']);
    Route::get('/admin/orders/{id}',      [OrderController::class, 'adminShow'])->where('id', '[0-9]+');
});
