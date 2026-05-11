<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }
        // 後台路由未登入時轉後台登入頁，前台路由轉前台登入頁
        if (str_starts_with($request->path(), 'admin/')) {
            return route('admin.login');
        }
        return route('login');
    }
}
