<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // 註冊
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $userRoleId = Role::where('name', 'user')->value('id');

        if (!$userRoleId) {
            return response()->json(['message' => '系統錯誤，請聯絡管理員'], 500);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->roles()->attach($userRoleId);

        Auth::login($user);
        $request->session()->regenerate();

        // 隱藏欄位獲取部分欄位
        return response()->json([
            'message' => '註冊成功',
            'user'    => $user->only(['id', 'name', 'email']),
        ], 201);
    }

    // 登入
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => ['帳號或密碼錯誤'],
            ]);
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

        // 隱藏欄位獲取部分欄位
        return response()->json([
            'message' => '登入成功',
            'user'    => $user->only(['id', 'name', 'email']),
        ]);
    }

    // 登出
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => '登出成功',
        ]);
    }

    // 取得當前使用者
    public function me(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        // 隱藏欄位獲取部分欄位
        return response()->json($user->only(['id', 'name', 'email']));
    }
}
