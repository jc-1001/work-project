<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            // 頁面路由優先（GET view route 需先於資料路由，避免被蓋掉）
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // 後台管理（具名路由比 web.php 萬用模式更具體，仍優先匹配）
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));

            // 前台購物
            Route::middleware('web')
                ->group(base_path('routes/shop.php'));

            // 保留 api.php 供未來對外公開 API 使用
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));
        });
    }
}
