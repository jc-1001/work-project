<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use PDO;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $pdo = new PDO(
            'sqlsrv:server=' . env('DB_HOST') . ',' . env('DB_PORT', '1433') . ';Database=' . env('DB_DATABASE'),
            env('DB_USERNAME'),
            env('DB_PASSWORD')
        );
        DB::connection()->setPdo($pdo);
    }
}