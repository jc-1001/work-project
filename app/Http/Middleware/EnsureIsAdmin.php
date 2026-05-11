<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsAdmin
{
    // 萬一有人直接在瀏覽器開 API 網址，也能給出合理的回應而不是一堆 JSON 文字
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->isAdmin()) {
            return $request->expectsJson()
                ? response()->json(['message' => '無權限'], 403)
                : redirect()->route('admin.login');
        }

        return $next($request);
    }
}
