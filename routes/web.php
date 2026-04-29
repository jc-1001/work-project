<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 前台固定路由
Route::get('/', fn () => view('app'));
Route::get('/login', fn () => view('app'));
Route::get('/register', fn () => view('app'));

// 前台商城列表
Route::get('/shop', fn () => view('app'));

// 前台商品詳情（ID 限數字）
Route::get('/shop/{id}', fn () => view('app'))
    ->where('id', '[0-9]+');

// 前台商品頁（ID 限數字）
Route::get('/products/{id}', fn () => view('app'))
    ->where('id', '[0-9]+');

// 後台固定路由
Route::get('/admin/login', fn () => view('app'));
Route::get('/admin', fn () => view('app'));

// 後台功能頁（只接受合法英文路徑名稱）
Route::get('/admin/{page}', fn () => view('app'))
    ->where('page', '[a-zA-Z\-]+');

// 後台詳情頁（功能名稱 + 數字 ID）
Route::get('/admin/{page}/{id}', fn () => view('app'))
    ->where('page', '[a-zA-Z\-]+')
    ->where('id', '[0-9]+');
