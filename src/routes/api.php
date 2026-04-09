<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Models\Product;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

// 公開路由
// Route::post → 監聽 POST 方法的請求
// '/register' → URL 是 /api/register（Laravel 自動加上 /api 前綴）
// [AuthController::class, 'register'] → 收到請求後，去執行 AuthController 裡的 register 方法

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::get('/products', function () {
    // 撈取所有啟用的商品
    return Product::where('is_active', 1)->get();
});

Route::get('/products/{id}', function ($id) {
    // 撈取單一商品
    // findOrFail 如果找不到 ID 會自動回傳 404
    return Product::where('is_active', 1)->findOrFail($id);
});
Route::post('/order_items', [OrderController::class, 'store']);

// 需要登入的路由
// middleware('auth:sanctum') 是守衛，意思是「這個路由需要帶有效的 token 才能進入」，沒有 token 會直接回傳 401 錯誤。

// --- 需要登入的路由 ---
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users',   [UserController::class, 'index']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);
    Route::post('/orders', [OrderController::class, 'store']);
    
});