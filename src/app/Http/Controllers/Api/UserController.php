<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;


class UserController extends Controller
// 這裡的 UserController 是一個新的控制器，主要負責處理與使用者相關的 API 請求，列出所有使用者、更新使用者資料等。它繼承了 Controller 類別，可以使用 Laravel 提供的基本功能。
{
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }
}