<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;


class UserController extends Controller
// 這裡的 UserController 是一個新的控制器，主要負責處理與使用者相關的 API 請求，列出所有使用者、更新使用者資料等。它繼承了 Controller 類別，可以使用 Laravel 提供的基本功能。
{
    // 列出所有使用者
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    // 實作更新方法
    public function update(Request $request)
    {

        // 告訴編輯器這是 User Model
        /** @var \App\Models\User $user */
        
        // 取得目前登入的使用者
        $user = auth()->user();

        // 驗證輸入資料
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // 執行更新
        $user->update($validated);

        return response()->json([
            'message' => '資料更新成功',
            'user' => $user
        ]);
    }
}
