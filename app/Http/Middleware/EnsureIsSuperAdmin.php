<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if (!$user || !$user->isSuperAdmin()) {
            return $request->expectsJson()
                ? response()->json(['message' => '無權限，僅限超級管理員存取'], 403)
                : redirect()->route('admin.forbidden');
        }

        return $next($request);
    }
}
