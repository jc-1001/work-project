<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if (!$user) {
            return $request->expectsJson()
                ? response()->json(['message' => '請先登入'], 401)
                : redirect()->route('admin.login');
        }

        if (!$user->isAdmin()) {
            return $request->expectsJson()
                ? response()->json(['message' => '無後台管理員權限'], 403)
                : redirect()->route('admin.login');
        }

        if (!$user->is_active) {
            return $request->expectsJson()
                ? response()->json(['message' => '帳號已停用'], 403)
                : redirect()->route('admin.login');
        }

        return $next($request);
    }
}
