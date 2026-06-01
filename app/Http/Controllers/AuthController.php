<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

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
            'password' => $request->password,
        ]);

        $user->roles()->attach($userRoleId);

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => '註冊成功',
            'user'    => $user->only(['id', 'name', 'email']),
        ], 201);
    }

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

        /** @var User $user */
        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ['帳號已停用，請聯絡管理員'],
            ]);
        }

        if ($user->isAdmin()) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ['管理員帳號請由後台登入'],
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => '登入成功',
            'user'    => $user->only(['id', 'name', 'email']),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => '登出成功',
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            /** @var \Illuminate\Auth\Passwords\PasswordBroker $broker */
            $broker   = Password::broker();
            $token    = $broker->createToken($user);
            $resetUrl = url('/reset-password') . '?token=' . $token . '&email=' . urlencode($user->email);
            Mail::to($user->email)->send(new ResetPasswordMail($resetUrl));
        }

        return response()->json(['message' => '若此 Email 已註冊，重設信件已寄出']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'                 => 'required',
            'email'                 => 'required|email',
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->password = $password;
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => '密碼已重設，請重新登入']);
        }

        return response()->json([
            'message' => match ($status) {
                Password::INVALID_TOKEN => '重設連結已失效或無效，請重新申請',
                Password::INVALID_USER  => '找不到此帳號',
                default                 => '密碼重設失敗，請重新申請',
            },
        ], 422);
    }

    public function me(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'user' => [
                'id'       => $user->id,
                'name'     => $user->name,
                'email'    => $user->email,
                'is_admin' => $user->isAdmin(),
            ],
        ]);
    }
}
