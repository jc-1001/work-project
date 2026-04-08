<?php
// PHP 的命名空間和引入套件的宣告，每個 Laravel 檔案開頭都會有
// 宣告這個檔案住在哪個資料夾
namespace App\Http\Controllers\Api;


// 引入父類別 Controller，下面的 class AuthController extends Controller 需要繼承它，才能使用 Laravel 提供的基本功能。
use App\Http\Controllers\Controller;

// 引入 User 模型，這樣才能用 User::create() 或 User::where() 操作資料庫的 users 資料表。
use App\Models\User;

// 引入 Request 類別，用來接收前端傳來的資料，例如 $request->email。
use Illuminate\Http\Request;

// 引入 Auth 門面（Facade），提供登入驗證功能，例如 Auth::attempt()。
use Illuminate\Support\Facades\Auth;

// 引入 Hash 門面，提供密碼加密功能，例如 Hash::make('password123')。
use Illuminate\Support\Facades\Hash;

// 引入驗證例外類別，當登入失敗時用來拋出錯誤訊息。
use Illuminate\Validation\ValidationException;


// 宣告這個 class 叫做 AuthController，並且繼承 Controller。extends 的意思是繼承，子類別可以使用父類別的所有功能。
class AuthController extends Controller
{
    // 註冊
    public function register(Request $request)
    {
        // 第一步：驗證輸入資料 
        // validate 是 Laravel 內建的驗證器，| 分隔多個規則：
        $request->validate([ //required → 不能為空
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            // confirmed → 必須有 password_confirmation 欄位且要一樣
            'password' => 'required|string|min:8|confirmed',
        ]);

        // 第二步：建立使用者
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password), // 密碼加密
        ]);

        // 第三步：產生 token
        // createToken() 是 Sanctum 提供的方法，會在 personal_access_tokens 資料表裡建立一筆記錄，並回傳 token 字串。
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'      => '註冊成功',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $user,
        ], 201);
    }

    // 登入
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Auth::attempt() 會：
        // 從資料庫找到這個 email 的使用者
        // 把輸入的密碼跟資料庫的加密密碼比對
        // 符合回傳 true，不符合回傳 false
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['帳號或密碼錯誤'],
            ]);
        }

        // Token 驗證原理
        // 登入成功 → 拿到 token → 之後每次請求都在 Header 帶上這個 token
        // → Laravel 驗證 token → 知道你是誰 → 允許存取

        $user  = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'      => '登入成功',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $user,
        ]);
    }

    // 登出
    public function logout(Request $request)
    {
        // 刪除目前使用的token，讓它失效，這樣就算前端還帶著這個 token 來請求，也會被拒絕。
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => '登出成功',
        ]);
    }

    // 取得當前使用者
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
